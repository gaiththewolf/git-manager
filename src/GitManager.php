<?php

namespace Gaiththewolf\GitManager;

use Symfony\Component\Process\Process;
use Gaiththewolf\GitManager\Exceptions\ComposerRunException;
use Gaiththewolf\GitManager\Exceptions\GitPasswordException;
use Gaiththewolf\GitManager\Data\GitStatus;
use Gaiththewolf\GitManager\Data\GitLog;
use Gaiththewolf\GitManager\Data\GitPull;
use Illuminate\Support\Collection;

class GitManager
{
    /**
     * Git executable location
     *
     * @var string
     */
    protected static $bin;
    protected static $workingDirectory;
    protected static $composerHome;
    protected static $sshPassword;

    private static $didInit = false;

    /** Set working directory and define git localtion
     * @param string $wd working directory - base_path()
     * @param ?string $ch composer home - base_path('vendor/bin/composer')
     * @param ?string $password ssh password
     */
    public static function init(?string $wd = null, ?string $ch = null, ?string $password = null) {
        if (!self::$didInit) {
            self::$didInit = true;
            if (file_exists('/usr/bin/git')) {
                self::$bin = '/usr/bin/git';
            } else {
                self::$bin = 'git';
            }
            self::$workingDirectory = $wd ?? base_path();
            self::$composerHome = $ch;
            self::$sshPassword = $password;
        }
    }

    /**
     * Sets git executable path
     *
     * @param string $path executable location
     */
    public static function setBin($path)
    {
        self::$bin = $path;
    }

    /**
     * Gets git executable path
     */
    public static function getBin()
    {
        return self::$bin;
    }

    /**
     * Sets up library for use in a default Windows environment
     */
    public static function windowsMode()
    {
        self::setBin('git');
    }

    /**
     * Set working path
     *
     * @param string $path to .git directory
     */
    public static function setWorkingDirectory($path)
    {
        self::$workingDirectory = $path;
    }

    /**
     * Gets working path
     */
    public static function getWorkingDirectory()
    {
        return self::$workingDirectory;
    }

    /**
     * Set composer home path
     *
     * @param string $path to .git directory
     */
    public static function setComposerHome($path)
    {
        self::$composerHome = $path;
    }

    /**
     * Get composer home path
     */
    public static function getComposerHome()
    {
        return self::$composerHome;
    }

    /**
     * Set ssh password
     *
     * @param string $path to .git directory
     */
    public static function setSshPassword($value)
    {
        self::$sshPassword = $value;
    }

    /**
     * Get ssh password
     */
    public static function getSshPassword()
    {
        return self::$sshPassword;
    }

    /**
     * run any command cmd - git or composer or artisan ...
     * @param array $args
     * @return mixed
     */
    public static function run(...$args):  ? string
    {
        $cmd = new Process($args);
        $cmd->setWorkingDirectory(self::$workingDirectory);
        if (isComposerCMD($args)) {
            if (self::$composerHome != null && !empty(self::$composerHome)) {
                $cmd->setEnv([
                    'COMPOSER_HOME' => self::$composerHome,
                    'APPDATA' => self::$composerHome,
                ]);
            } else {
                throw new ComposerRunException("Composer home not defined !");
            }
        }
        $cmd->setTimeout(null);
        $cmd->run();
        if ($cmd->isSuccessful()) {
            return $cmd->getOutput();
        }
        // Command execution failed
        return $cmd->getErrorOutput();
    }

    /**
     * run git cmd
     * @param ?string $password - for git ssh access
     * @param array $args
     * @return mixed
     */
    public static function cmd( ? string $password = null, ...$args) : ? string
    {
        array_unshift($args, self::getBin());
        $cmd = new Process($args);
        $cmd->setWorkingDirectory(self::$workingDirectory);
        $cmd->setTimeout(null);
        $cmd->run();
        if ($cmd->isSuccessful()) {
            return $cmd->getOutput();
        }
        // Check if password prompt is detected in the command output
        $output = $cmd->getOutput();
        $errorOutput = $cmd->getErrorOutput();

        if (isPassworPrompt($output) || isPassworPrompt($errorOutput)) {
            // If password prompt is detected, provide the password to continue
            if (empty($password) && empty(self::$sshPassword)) {
                throw new GitPasswordException("SSH password needed !");
            }
            $pass = empty($password) ? self::$sshPassword : $password;
            $cmd->setInput($pass . "\n");
            $cmd->run();

            if ($cmd->isSuccessful()) {
                return $cmd->getOutput();
            }
        }

        // Command execution failed
        return $cmd->getErrorOutput();
    }

    public static function version() : string
    {
        return self::cmd(null, 'version');
    }

    public static function lastUpdate() : string
    {
        return self::cmd(null, 'log', '-1', '--date=format:%Y/%m/%d %T', '--format=%ad');
    }

    public static function parseStatus($output) : GitStatus
    {
        // Extract the branch name
        preg_match('/On branch (\S+)/', $output, $branchMatches);
        return new GitStatus(
            $branchMatches[1],
            (strpos(strtolower($output), "branch is up to date") !== false),
            (strpos(strtolower($output), "publish your local commits") !== false),
            (strpos(strtolower($output), "update your local branch") !== false)
        );
    }

    public static function status( ? string $password = null)
    {
        self::cmd($password, 'fetch');
        return self::parseStatus(self::cmd(null, 'status'));
    }

    public static function parseLog($log) : Collection
    {
        $lines = explode("\n", $log);
        $collection = collect();
        $gitLog = new GitLog();
        foreach ($lines as $key => $line) {
            if (strpos($line, 'commit') === 0 || $key + 1 == count($lines)) {
                if (!$gitLog->isEmpty()) {
                    $gitLog->setMessage(substr($gitLog->message, 4));
                    $collection->add($gitLog);
                    $gitLog = new GitLog();
                }
                $gitLog->setHash(substr($line, strlen('commit') + 1));
            } else if (strpos($line, 'Author') === 0) {
                $pattern = '/^(.+)\s+<(.+)>$/';
                preg_match($pattern, substr($line, strlen('Author:') + 1), $matches);
                $fullName = $matches[1];
                $email = $matches[2];
                $gitLog->setAuthor(['full_name' => $fullName, 'email' => $email]);
            } else if (strpos($line, 'Date') === 0) {
                $gitLog->setDate(substr($line, strlen('Date:') + 3));
            } elseif (strpos($line, 'Merge') === 0) {
                $merge = substr($line, strlen('Merge:') + 1);
                $gitLog->setMerge(explode(' ', $merge));
            } else {
                if (!empty($gitLog->message)) {
                    $gitLog->appendMessage($line) ;
                } else {
                    $gitLog->setMessage($line) ;
                }
            }
        }
        return $collection;
    }

    public static function log(int $logNum = 10)
    {
        return self::parseLog(self::cmd(null, 'log', "-$logNum"));
    }

    public static function pull( ? string $password = null) : GitPull
    {
        $output = self::cmd($password, 'pull');
        $filesPattern = '/\n(.*?)\s+\|/';
        preg_match_all($filesPattern, $output, $filesMatches);
        $changeCountPattern = '/(\d+)\s+file(s)?\s+changed/';
        preg_match($changeCountPattern, $output, $changeCountMatches);
        $insertionCountPattern = '/(\d+)\s+insertion(s)?\(\+\)/';
        preg_match($insertionCountPattern, $output, $insertionCountMatches);
        $deletionCountPattern = '/(\d+)\s+deletion(s)?\(-\)/';
        preg_match($deletionCountPattern, $output, $deletionCountMatches);
        return new GitPull(
            isset($filesMatches[1]) ? $filesMatches[1] : [],
            (strpos(strtolower($output), "already up to date") !== false),
            isset($changeCountMatches[1]) ? (int) $changeCountMatches[1] : 0,
            isset($insertionCountMatches[1]) ? (int) $insertionCountMatches[1] : 0,
            isset($deletionCountMatches[1]) ? (int) $deletionCountMatches[1] : 0
        );
    }
}
