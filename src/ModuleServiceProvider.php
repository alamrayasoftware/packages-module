<?php

namespace Arsoft\Module;

use Illuminate\Support\ServiceProvider;
use Arsoft\Module\Commands\initCommand;
use Arsoft\Module\Commands\initModuleBackendCommand;
use Arsoft\Module\Commands\makeCommand;
use Arsoft\Module\Commands\makeModuleBackendCommand;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // publish config file
        
            $this->commands([
                initCommand::class,
                makeCommand::class,
                initModuleBackendCommand::class,
                makeModuleBackendCommand::class,
            ]);
        }
    }
}
