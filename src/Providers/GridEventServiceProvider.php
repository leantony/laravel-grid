<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Leantony\Grid\Events\UserActionRequested;
use Leantony\Grid\Listeners\HandleUserAction;

class GridEventServiceProvider extends EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserActionRequested::class => [
            HandleUserAction::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}