<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Export;

use Illuminate\Support\Collection;

class HtmlExport implements GridExportInterface
{
    /**
     * Export data from the grid
     *
     * @param Collection $data
     * @param array $args
     * @return mixed
     */
    public function export($data, array $args)
    {
        $exportableColumns = $args['exportableColumns'];
        $fileName = $args['fileName'];
        $exportView = $args['exportView'];

        return response()->streamDownload(function () use ($data, $exportableColumns, $exportView) {
            echo view($exportView, [
                'columns' => $exportableColumns,
                'data' => $data->toArray()
            ])->render();
        }, $fileName . '.html');
    }
}