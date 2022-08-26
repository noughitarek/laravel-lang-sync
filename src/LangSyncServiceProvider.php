<?php

namespace Noughitarek\LaravelLangSync;

use Illuminate\Support\ServiceProvider;

class LangSyncServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }
    public function register()
    {
        if ($this->app->runningInConsole())
        {
            $this->commands(LangSyncCommands::class);
        }
    }
}