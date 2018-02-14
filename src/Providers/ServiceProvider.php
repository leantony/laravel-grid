<?php

namespace Leantony\Grid\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Leantony\Grid\Commands\GenerateGrid;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadHelpers();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'leantony');

        $this->publishes([
            __DIR__ . '/../resources/config/grids.php' => config_path('grids.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/leantony/grid')
        ], 'views');

        $this->publishes([
            __DIR__ . '/../resources/assets' => base_path('public/vendor/leantony/grid')
        ], 'assets');
    }

    /**
     * Load helper function files
     */
    protected function loadHelpers()
    {
        $files = glob(__DIR__ . '/../Helpers/*.php');
        foreach ($files as $file) {
            require_once($file);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(GenerateGrid::class);
    }
}
