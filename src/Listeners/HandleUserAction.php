<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Leantony\Grid\Events\UserActionRequested;
use Leantony\Grid\Filters\SearchDataHandler;
use Leantony\Grid\Filters\SortDataHandler;

class HandleUserAction
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserActionRequested $event
     * @return mixed
     * @throws \Throwable
     */
    public function handle(UserActionRequested $event)
    {
        if(!empty($event->request->query())) {

            if ($event->request->has($event->grid->getSearchParam())) {
                // search
                (new SearchDataHandler(
                    $event->grid, $event->request,
                    $event->builder, $event->validTableColumns, $event->args
                ))->searchRows();

            } else {
                // filter
                (new RowFilterHandler(
                    $event->grid, $event->request,
                    $event->builder, $event->validTableColumns, $event->args
                ))->filterRows();
            }

            // sort
            (new SortDataHandler(
                $event->grid, $event->request,
                $event->builder, $event->validTableColumns
            ))->sort();
        }

        // export
        if ($event->request->has($event->grid->getExportParam())) {
            // do not export at this point
            return [
                'exporter' => (new DataExportHandler(
                    $event->grid, $event->request,
                    $event->builder, $event->validTableColumns, $event->args
                )),
                'data' => (new GridPaginationHandler($event->request, $event->builder))->paginate()
            ];
        }
        // paginate
        return (new GridPaginationHandler($event->request, $event->builder))->paginate();
    }
}