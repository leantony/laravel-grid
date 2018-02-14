<?php

namespace Leantony\Grid;

use Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

trait ExportsData
{
    /**
     * Max export rows
     *
     * @var int
     */
    protected static $MAX_EXPORT_ROWS = 50000;

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
     * Export to data to excel. Also works with pdf and word
     *
     * @return $this
     * @throws \Exception
     */
    public function exportExcel()
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
     * Gets the rows to be exported
     *
     * @return array
     */
    abstract public function getColumnsToExport();

    /**
     * Download export data
     *
     * @param string $type
     */
    public function downloadExportedAs($type = 'xlsx')
    {
        if($type === 'pdf') {
            // requires https://github.com/barryvdh/laravel-snappy
            $pdf = app('snappy.pdf.wrapper');

//            dd($this->processedRows, $this->dataForExport->toArray());
            $pdf->loadHtml(view('leantony::grid.reports.pdf_report', [
                'title' => 'Pdf Report',
                'rows' => $this->getProcessedColumns(),
                'data' => $this->dataForExport->toArray(),
            ])->render());

            return $pdf->inline();
        } else {
            // use excel export
            $this->excelWriter->export($type);
        }
    }

    /**
     * Get the data to be exported
     *
     * @return Collection|array
     */
    abstract protected function getExportData();
}