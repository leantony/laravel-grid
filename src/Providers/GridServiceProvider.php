<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Providers;

use Event;
use Blade;
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
    }

    /**
     * Register custom blade directives
     *
     * @return void
     */
    public function registerBladeDirectives()
    {
        Blade::directive('modalBegin', function ($data) {
            $view = 'leantony::modal.modal-partial-start';

            if (!$data || !is_array($data)) {
                throw new \InvalidArgumentException("data is undefined.");
            }

            return "<?php echo \$__env->make('{$view}', array_except(get_defined_vars(), ['__data', '__path']))->with{$data}->render(); ?>";
        });

        Blade::directive('modalEnd', function () {
            $view = 'leantony::modal.modal-partial-end';

            return "<?php echo \$__env->make('{$view}', array_except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
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
}
