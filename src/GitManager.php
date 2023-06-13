<?php

namespace Gaiththewolf\GitManager;

use Symfony\Component\Process\Process;
use Exception;
use Gaiththewolf\GitManager\Exceptions\ComposerRunException;
use Gaiththewolf\GitManager\Exceptions\GitPasswordException;

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

    /** Set working directory and define git localtion
     * @param string $wd working directory - base_path()
     * @param ?string $ch composer home - base_path('vendor/bin/composer')
     */
    public function __construct(string $wd, ?string $ch = null)
    {
        if (file_exists('/usr/bin/git')) {
            self::$bin = '/usr/bin/git';
        } else {
            self::$bin = 'git';
        }
        self::$workingDirectory = $wd;
        self::$composerHome = $ch;
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
    public static function cmd( ? string $password = null, ...$args) :  ? string
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
            if (empty($password) || $password == null) {
                throw new GitPasswordException("SSH password needed !");
            }
            $cmd->setInput($password . "\n");
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

    public static function parseStatus($output) : array
    {
        $parsedData = [];
        // Extract the branch name
        preg_match('/On branch (\S+)/', $output, $branchMatches);
        $parsedData['branch'] = $branchMatches[1];
        // Check if the branch is up to date
        $parsedData['is_up_to_date'] = (strpos(strtolower($output), "branch is up to date") !== false);
        $parsedData['is_push'] = (strpos(strtolower($output), "publish your local commits") !== false);
        $parsedData['is_pull'] = (strpos(strtolower($output), "update your local branch") !== false);
        return $parsedData;
    }

    public static function status( ? string $password = null)
    {
        self::cmd($password, 'fetch');
        return self::parseStatus(self::cmd(null, 'status'));
    }

    public static function parseLog($log)
    {
        $lines = explode("\n", $log);
        $history = array();
        foreach ($lines as $key => $line) {
            if (strpos($line, 'commit') === 0 || $key + 1 == count($lines)) {
                if (!empty($commit)) {
                    $commit['message'] = substr($commit['message'], 4);
                    array_push($history, $commit);
                    unset($commit);
                }
                $commit['hash'] = substr($line, strlen('commit') + 1);
            } else if (strpos($line, 'Author') === 0) {
                $commit['author'] = substr($line, strlen('Author:') + 1);
            } else if (strpos($line, 'Date') === 0) {
                $commit['date'] = substr($line, strlen('Date:') + 3);
            } elseif (strpos($line, 'Merge') === 0) {
                $commit['merge'] = substr($line, strlen('Merge:') + 1);
                $commit['merge'] = explode(' ', $commit['merge']);
            } else {
                if (isset($commit['message'])) {
                    $commit['message'] .= $line;
                } else {
                    $commit['message'] = $line;
                }
            }
        }
        return $history;
    }

    public static function log(int $logNum = 10)
    {
        return self::parseLog(self::cmd(null, 'log', "-$logNum"));
    }

    public static function pull( ? string $password = null)
    {
        $output = self::cmd($password, 'pull');
        $pullOut = [];

        $pullOut['is_up_to_date'] = (strpos(strtolower($output), "already up to date") !== false);

        $filesPattern = '/\n(.*?)\s+\|/';
        preg_match_all($filesPattern, $output, $filesMatches);
        $pullOut["files"] = isset($filesMatches[1]) ? $filesMatches[1] : [];

        $changeCountPattern = '/(\d+)\s+file(s)?\s+changed/';
        preg_match($changeCountPattern, $output, $changeCountMatches);
        $pullOut["changed"] = isset($changeCountMatches[1]) ? (int) $changeCountMatches[1] : 0;

        $insertionCountPattern = '/(\d+)\s+insertion(s)?\(\+\)/';
        preg_match($insertionCountPattern, $output, $insertionCountMatches);
        $pullOut["insertion"] = isset($insertionCountMatches[1]) ? (int) $insertionCountMatches[1] : 0;

        $deletionCountPattern = '/(\d+)\s+deletion(s)?\(-\)/';
        preg_match($deletionCountPattern, $output, $deletionCountMatches);
        $pullOut["deletion"] = isset($deletionCountMatches[1]) ? (int) $deletionCountMatches[1] : 0;

        return $pullOut;
    }
}
