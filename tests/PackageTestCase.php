<?php
declare(strict_types=1);

namespace Gaiththewolf\GitManager\Tests;

use Gaiththewolf\GitManager\GitManager;
use Gaiththewolf\GitManager\GitManagerFacade;
use Gaiththewolf\GitManager\GitManagerServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GitManager::init(__DIR__);
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
        $this->assertNotEmpty($gitBin);

        $getWorkingDirectory = GitManager::getWorkingDirectory();
        $this->assertEquals($getWorkingDirectory, __DIR__);

        /*$gitVersion = GitManager::version();
        $this->assertNotEmpty($gitVersion);

        $gitLogs = GitManager::log(2);
        $this->assertIsIterable($gitLogs);*/
    }
}
