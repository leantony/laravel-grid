<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Export;

use Excel;
use Illuminate\Support\Collection;
use Leantony\Grid\Filters\DefaultExport;

class ExcelExport implements GridExportInterface
{
    public function __construct()
    {
    }

    /**
     * Export data from the grid
     *
     * @param Collection $data
     * @param array $args
     * @return mixed
     */
    public function export($data, array $args)
    {
        $fileName = $args['fileName'];
        $gridName = $args['gridShortName'];
        $exportableColumns = $args['exportableColumns'];
        $type = $args['exportType'];

        $exporter = new DefaultExport(
            sprintf('%s report', $gridName),
            $exportableColumns,
            $data->toArray()
        );

        return Excel::download($exporter, $fileName . '.' . $type);
    }
}