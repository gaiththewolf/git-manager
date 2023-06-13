<?php
declare(strict_types=1);
 
namespace Gaiththewolf\GitManager\Tests;

use Gaiththewolf\GitManager\GitManager;
use Gaiththewolf\GitManager\GitManagerFacade;
use Gaiththewolf\GitManager\GitManagerServiceProvider;
use PHPUnit\Framework\TestCase;

class PackageTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GitManager::windowsMode();
        GitManager::setWorkingDirectory(__DIR__);
    }
    
    protected function getPackageProviders($app): array
    {
        return [
            GitManagerServiceProvider::class,
        ];
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $gitBin = GitManager::getBin();
        $this->assertEquals($gitBin, "git");

        $getWorkingDirectory = GitManager::getWorkingDirectory();
        $this->assertEquals($getWorkingDirectory, __DIR__);

        $gitVersion = GitManager::version();
        $this->assertNotEmpty($gitVersion);

        $gitLogs = GitManager::log(2);
        $this->assertIsArray($gitLogs);
    }
}