<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use Leantony\Grid\Commands\GenerateGrid;

class GridServiceProvider extends ServiceProvider
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
            __DIR__ . '/../resources/config/grid.php' => config_path('grid.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/leantony')
        ], 'views');

        $this->publishes([
            __DIR__ . '/../resources/assets' => base_path('public/vendor/leantony/grid')
        ], 'assets');

        // events
        Event::listen('grid.fetch_data', 'Leantony\\Grid\\Listeners\\HandleUserAction@handle');
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
