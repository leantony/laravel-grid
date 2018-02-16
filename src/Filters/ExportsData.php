<?php

namespace Leantony\Grid\Filters;

use Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use PDF;

trait ExportsData
{
    /**
     * Max export rows. More = slower export process
     *
     * @var int
     */
    protected static $MAX_EXPORT_ROWS = 50000;
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
    protected $exportFilename = null;

    /**
     * The excel writer instance
     *
     * @var LaravelExcelWriter
     */
    protected $excelWriter = null;

    /**
     * The data to be exported
     *
     * @var Collection
     */
    protected $dataForExport = null;

    /**
     * Prepare export data
     *
     * @return $this
     * @throws \Exception
     */
    public function prepareData()
    {
        if (class_exists(LaravelExcelWriter::class)) {
            $instance = $this;

            $this->exportFilename = $this->getFileNameForExport();

            $data = $this->getExportData();

            $this->dataForExport = $data;

            $this->excelWriter = Excel::create($this->exportFilename, function ($excel) use ($data, $instance) {
                /** @var $excel LaravelExcelWriter */
                $instance->makeExcelFromGridData($excel, $data);
            });
            return $this;
        }
        throw new \Exception("Please ensure that the class Maatwebsite\\Excel\\Writers\\LaravelExcelWriter exists.");
    }

    /**
     * Filename for export
     *
     * @return string
     */
    public function getFileNameForExport()
    {
        $this->exportFilename = Str::slug($this->getName()) . '-' . time();
        return $this->exportFilename;
    }

    /**
     * Get the data to be exported
     *
     * @return Collection
     */
    public function getExportData()
    {
        // work on the underlying query instance
        // this one has already passed through the filter
        $values = $this->getQuery()->take(static::$MAX_EXPORT_ROWS)->get();

        $columns = collect($this->getColumnsToExport())->reject(function ($v) {
            // reject all columns that have been set as not exportable
            return !$v->export;
        })->toArray();

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
    public function getColumnsToExport()
    {
        return $this->getProcessedColumns();
    }

    /**
     * Make an excel worksheet
     *
     * @param LaravelExcelWriter $excel
     * @param Collection $data
     */
    protected function makeExcelFromGridData($excel, $data)
    {
        $max = static::$MAX_EXPORT_ROWS;

        $excel->sheet('sheet1', function (LaravelExcelWorksheet $sheet) use ($data, $max) {

            $sheet->fromModel($data);
        });
    }

    /**
     * Download export data
     *
     * @param string $type
     * @return mixed
     * @throws \Throwable
     * @throws \Maatwebsite\Excel\Exceptions\LaravelExcelException
     */
    public function downloadAs($type = 'xlsx')
    {
        if ($type === 'pdf') {
            return $this->exportPdf();
        } else {
            // this handles any other types, so we pass the type in
            $this->exportGeneral($type);
        }
    }

    /**
     * Export to PDF
     *
     * @return mixed
     * @throws \Throwable
     */
    public function exportPdf()
    {
        // requires https://github.com/barryvdh/laravel-snappy
        $pdf = PDF::loadView($this->getExportToPdfView(), [
            'title' => sprintf('%s report', $this->shortSingularGridName()),
            'columns' => $this->getProcessedColumns(),
            'data' => $this->dataForExport,
        ]);

        return $pdf->download($this->getFileNameForExport() . '.pdf');
    }

    /**
     * Get the html view used for PDF export
     *
     * @return string
     */
    protected function getExportToPdfView(): string
    {
        return 'leantony::reports.pdf_report';
    }

    /**
     * Export to a general type
     *
     * @param $type
     * @throws \Maatwebsite\Excel\Exceptions\LaravelExcelException
     */
    public function exportGeneral($type)
    {
        $this->excelWriter->export($type);
    }
}