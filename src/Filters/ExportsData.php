<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Excel;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait ExportsData
{
    /**
     * Max export rows. More = slower export process
     *
     * @var int
     */
    protected $maxExportRows = 50000;

    /**
     * Quick toggle to specify if the grid allows exporting of records
     *
     * @var bool
     */
    protected $allowsExporting = true;

    /**
     * The filename that would be exported
     *
     * @var string
     */
    protected $exportFilename;

    /**
     * Available columns for export
     *
     * @var array|null
     */
    protected $availableColumnsForExport = null;

    /**
     * Download export data
     *
     * @param string $type any of xlsx, xls, csv or pdf
     * @return Response
     * @throws \Throwable
     */
    public function exportAs($type = 'xlsx')
    {
        $e = new DefaultExport(
            sprintf('%s report', $this->shortSingularGridName()),
            $this->getExportableColumns()[1], // columns are at index 1
            $this->getExportData()->toArray()
        );

        return Excel::download($e, $this->getFileNameForExport() . '.' . $type);
    }

    /**
     * Get the data to be exported
     *
     * @return Collection
     */
    public function getExportData(): Collection
    {
        list($pinch, $columns) = $this->getExportableColumns();

        // works on the underlying query instance
        $values = $this->getQuery()->take($this->maxExportRows)->get();

        // customize the results
        $data = $values->map(function ($v) use ($columns) {
            $data = [];
            foreach ($columns as $column) {
                // render as per requested on each column
                // `processColumns()` would have already taken care of processing the callbacks
                // so here, we only pass the required arguments
                if (is_callable($column->data)) {
                    array_push($data, [$column->name => call_user_func($column->data, $v, $column->key)]);
                } else {
                    array_push($data, [$column->name => $v->{$column->key}]);
                }
            }
            // collapse the data by a single level
            return collect($data)->collapse()->toArray();
        });

        return $data;
    }

    /**
     * Gets the columns to be exported
     *
     * @return array
     */
    public function getColumnsToExport(): array
    {
        return $this->getProcessedColumns();
    }

    /**
     * Filename for export
     *
     * @return string
     */
    public function getFileNameForExport(): string
    {
        $this->exportFilename = Str::slug($this->getName()) . '-' . time();
        return $this->exportFilename;
    }

    /**
     * Check if the user wants to export data
     *
     * @return bool
     */
    protected function wantsToExport(): bool
    {
        return $this->request->has($this->exportParam) && $this->allowsExporting;
    }

    /**
     * Get exportable columns by skipping the ones that were not requested
     *
     * @return array
     */
    protected function getExportableColumns(): array
    {
        if ($this->availableColumnsForExport !== null) {
            return $this->availableColumnsForExport;
        }

        $pinch = [];
        $availableColumns = $this->getColumnsToExport();
        $columns = collect($availableColumns)->reject(function ($v) use (&$pinch) {
            // reject all columns that have been set as not exportable
            $canBeSkipped = !$v->export;
            if (!$canBeSkipped) {
                // add this to an array to be used below for granular selection
                $pinch[] = $v->key;
            }
            return $canBeSkipped;
        });
        $this->availableColumnsForExport = [$pinch, $columns];
        return $this->availableColumnsForExport;
    }
}