<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Leantony\Grid\Commands\GenerateGrid;
use Leantony\Grid\Modal\ModalRenderer;

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

        $this->registerCustomEvents();
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
        $this->registerServices();
    }

    /**
     * Register app services
     *
     * @return void
     */
    public function registerServices()
    {
        $this->app->singleton('modal', function ($app) {
            return new ModalRenderer();
        });
    }

    /**
     * Register custom events
     *
     * @return void
     */
    public function registerCustomEvents(): void
    {
        // events
        Event::listen('grid.fetch_data', 'Leantony\\Grid\\Listeners\\HandleUserAction@handle');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['modal'];
    }
}
