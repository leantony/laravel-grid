<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Export;

use Illuminate\Support\Collection;

class PdfExport implements GridExportInterface
{
    /**
     * Export data from the grid
     *
     * @param Collection $data
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public function export($data, array $args)
    {
        $exportableColumns = $args['exportableColumns'];
        $fileName = $args['fileName'];
        $exportView = $args['exportView'];
        $title = $args['title'];

        if(class_exists(\Barryvdh\DomPDF\ServiceProvider::class)) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML(view($exportView, ['title' => $title, 'columns' => $exportableColumns, 'data' => $data])->render());
            return $pdf->download($fileName . '.pdf');
        }
        throw new \InvalidArgumentException("PDF library not found. Please install 'barryvdh/laravel-dompdf' to handle PDF exports");
    }
}