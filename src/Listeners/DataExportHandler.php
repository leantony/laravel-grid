<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Leantony\Grid\Export\DefaultExporter;
use Leantony\Grid\Export\ExcelExport;
use Leantony\Grid\Export\ExcelExporter;
use Leantony\Grid\Export\HtmlExport;
use Leantony\Grid\Export\JsonExport;
use Leantony\Grid\Export\PdfExport;
use Leantony\Grid\GridInterface;
use Leantony\Grid\GridResources;

class DataExportHandler
{
    use GridResources;

    /**
     * Quick toggle to specify if the grid allows exporting of records
     *
     * @var bool
     */
    protected $allowsExporting = true;

    /**
     * Query chunk size for export
     *
     * @var int
     */
    protected $chunkSize = 200;

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
     * DataExportHandler constructor.
     * @param GridInterface $grid
     * @param Request $request
     * @param $builder
     * @param $validTableColumns
     * @param $args
     */
    public function __construct(GridInterface $grid, Request $request, $builder, $validTableColumns, $args)
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->query = $builder;
        $this->validGridColumns = $validTableColumns;
        $this->args = $args;
    }

    /**
     * Export the data
     *
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function export()
    {
        if ($this->wantsToExport()) {
            $param = $this->request->get($this->getGrid()->getGridExportParam());
            if (in_array($param, $this->getGrid()->getGridExportTypes())) {
                return $this->exportAs($param);
            }
        }
    }

    /**
     * Check if the user wants to export data
     *
     * @return bool
     */
    protected function wantsToExport(): bool
    {
        return $this->getRequest()->has($this->getGrid()->getGridExportParam()) && $this->allowsExporting;
    }

    /**
     * Download export data
     *
     * @param string $type any of an allowed type in configuration
     * @return Response
     * @throws \Throwable
     */
    public function exportAs($type = 'xlsx')
    {
        switch ($type) {
            case 'pdf': {
                return (new PdfExport())->export($this->getExportData(), [
                    'exportableColumns' => $this->getExportableColumns()[1],
                    'fileName' => $this->getFileNameForExport(),
                    'exportView' => $this->getGridExportView(),
                ]);
            }
            case 'csv':
            case 'xlsx':
                {
                    $columns = $this->getExportableColumns()[1];
                    // headings
                    $headings = $columns->map(function ($col) {
                        return $col->name;
                    })->toArray();

                    return (new ExcelExport([
                        'title' => $this->getGrid()->getName(),
                        'columns' => $columns,
                        'data' => $this->getExportData(),
                        'headings' => $headings,
                    ]))->download($this->getFileNameForExport() . '.' . $type);
                }
            case 'html':
                {
                    return (new HtmlExport())->export($this->getExportData(), [
                        'exportableColumns' => $this->getExportableColumns()[1],
                        'fileName' => $this->getFileNameForExport(),
                        'exportView' => $this->getGridExportView(),
                    ]);
                }
            case 'json':
                {
                    return (new JsonExport())->export($this->getExportData(['doNotFormatKeys' => true]), [
                        'fileName' => $this->getFileNameForExport(),
                    ]);
                }
            default:
                throw new \InvalidArgumentException("unknown export type");
        }
    }

    /**
     * Get exportable columns by skipping the ones that were not requested
     *
     * @return array
     * @throws \Exception
     */
    protected function getExportableColumns(): array
    {
        if ($this->availableColumnsForExport !== null) {
            return $this->availableColumnsForExport;
        }

        $pinch = [];
        $availableColumns = $this->getColumnsToExport();
        $columns = collect($availableColumns)->reject(function ($column) use (&$pinch) {
            // reject all columns that have been set as not exportable
            $canBeSkipped = !$column->export;
            if (!$canBeSkipped) {
                // add this to an array to be used for granular filtering of the query
                $pinch[] = $column->key;
            }
            return $canBeSkipped;
        });
        $this->availableColumnsForExport = [$pinch, $columns];
        return $this->availableColumnsForExport;
    }

    /**
     * Gets the columns to be exported
     *
     * @return array
     * @throws \Exception
     */
    public function getColumnsToExport(): array
    {
        return $this->getGrid()->getProcessedColumns();
    }

    /**
     * Filename for export
     *
     * @return string
     */
    public function getFileNameForExport(): string
    {
        $this->exportFilename = Str::slug($this->getGrid()->getName()) . '-' . time();
        return $this->exportFilename;
    }

    /**
     * Get the data to be exported
     *
     * @param array $params
     * @return Collection
     * @throws \Exception
     */
    public function getExportData(array $params = []): Collection
    {
        $useUnformattedKeys = $params['doNotFormatKeys'] ?? false;

        list($pinch, $columns) = $this->getExportableColumns();

        $records = new Collection();

        $this->getQuery()->select($pinch)->chunk(200, function($items) use ($columns, $params, $useUnformattedKeys, $records){
            // customize the results
            $columns = $columns->toArray();

            $data = $items->map(function ($value) use ($columns, $params, $useUnformattedKeys) {
                return call_user_func([$this, 'dataFormatter'], $value, $columns, $useUnformattedKeys);
            });
            $records->push($data);
        });

        return $records[0];
    }

    /**
     * Format data for export
     *
     * @param mixed $item
     * @param array $columns
     * @param boolean $useUnformattedKeys
     * @return array
     */
    protected function dataFormatter($item, array $columns, bool $useUnformattedKeys): array
    {
        $data = [];
        foreach ($columns as $column) {
            // render as per requested on each column
            // `processColumns()` would have already taken care of processing the callbacks
            // so here, we only pass the required arguments
            if (is_callable($column->data)) {
                $key = $useUnformattedKeys ? $column->key : $column->name;
                $value = call_user_func($column->data, $item, $column->key);
                array_push($data, [$key => $value]);
            } else {
                $key = $useUnformattedKeys ? $column->key : $column->name;
                $value = $item->{$column->key};
                array_push($data, [$key => $value]);
            }
        }
        // collapse the data to a 1d array
        return collect($data)->collapse()->toArray();
    }
}