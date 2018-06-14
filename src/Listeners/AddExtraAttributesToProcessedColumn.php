<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Leantony\Grid\Events\ColumnProcessed;

class AddExtraAttributesToProcessedColumn
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
     * @param  ColumnProcessed $event
     * @return mixed
     * @throws \Throwable
     */
    public function handle(ColumnProcessed $event)
    {
        $col = $event->column;
        $name = $event->columnName;
        $data = $event->columnData;

        $this->addHtmlCheckForLabel($data, $col);

        // make sure the column object is returned
        return $col;
    }

    /**
     * Allow use of raw html for column label
     *
     * @param $columnData
     * @param $col
     */
    public function addHtmlCheckForLabel($columnData, $col): void
    {
        if (isset($columnData['label'])) {
            $useRawHtmlForLabel = $columnData['label']['raw'] ?? false;
        } else {
            $useRawHtmlForLabel = false;
        }

        $col->useRawHtmlForLabel = $useRawHtmlForLabel;
    }
}