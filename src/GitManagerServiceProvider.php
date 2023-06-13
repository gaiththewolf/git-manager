<?php

namespace Gaiththewolf\GitManager;

use Illuminate\Support\ServiceProvider;

class GitManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('git-manager.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'git-manager');

        // Register the main class to use with the facade
        $this->app->singleton('git-manager', function () {
            return new GitManager;
        });
    }
}
