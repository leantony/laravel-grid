<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Columns;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait CreatesColumns
{
    /**
     * The columns that appear on the table headers. Specified as $key => $value
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The columns that have been processed
     *
     * @var array
     */
    protected $processedColumns = [];

    /**
     * Columns to be used as search
     *
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * Return the columns to be displayed on the grid
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get the processed columns
     *
     * @return array
     * @throws \Exception
     */
    public function getProcessedColumns(): array
    {
        return $this->processColumns();
    }

    /**
     * Process the columns that were supplied
     *
     * @return array
     * @throws \Exception
     */
    public function processColumns()
    {
        if (!empty($this->processedColumns)) {
            return $this->processedColumns;
        }
        $columns = [];
        // process
        foreach ($this->columns as $columnName => $columnData) {

            // string in place of column data
            if (!is_array($columnData)) {
                $columnData = $this->getGridDefaultColumnDataOptions();
            }
            // should render
            if (!$this->canRenderColumn($columnName, $columnData)) {
                continue;
            }

            // css styles
            $styles = $this->fetchCssStyles($columnName, $columnData);

            $columnClass = $styles['columnClass'];

            $rowClass = $styles['rowClass'];

            // label
            $label = $this->fetchColumnLabel($columnName, $columnData)['label'];

            // searchable columns
            $searchable = $this->fetchSearchableColumns($columnName, $columnData)['searchable'];

            // filter
            $filter = $this->fetchColumnFilter($columnName, $columnData)['filter'];

            // data
            $data = $this->fetchColumnData($columnName, $columnData)['data'];

            // once we are done, push to columns array
            array_push($columns, new Column([
                'name' => $label,
                'key' => $columnName,
                'data' => $data,
                'searchable' => $searchable,
                'rowClass' => $rowClass,
                'columnClass' => $columnClass,
                'sortable' => $columnData['sort'] ?? true,
                'filter' => $filter,
                'raw' => $columnData['raw'] ?? false,
                'export' => $columnData['export'] ?? true,
            ]));
        }

        $this->processedColumns = $columns;

        return $this->processedColumns;
    }

    /**
     * Determine if a column can be rendered on the grid
     *
     * @param string $columnName
     * @param array $columnData
     * @return bool
     */
    public function canRenderColumn(string $columnName, array $columnData)
    {
        if (isset($columnData['renderIf']) && is_callable($columnData['renderIf'])) {
            $func = $columnData['renderIf'];
            // when the callback returns false, then skip this row
            if (!call_user_func($func, $columnData)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetch css styles
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function fetchCssStyles($columnName, $columnData)
    {
        // css
        if (isset($columnData['styles'])) {
            $classAttributes = $columnData['styles'];
            $columnClass = $classAttributes['column'] ?? '';
            $rowClass = $classAttributes['row'] ?? '';
        } else {
            $columnClass = '';
            $rowClass = '';
        }

        return compact('columnClass', 'rowClass');
    }

    /**
     * Extract the column label
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function fetchColumnLabel(string $columnName, array $columnData)
    {
        if (isset($columnData['label'])) {
            $label = $columnData['label'];
        } else {
            $label = ucwords(preg_replace($this->getGridLabelNamePattern(), ' ', $columnName));
        }

        return compact('label');
    }

    /**
     * Extract searchable columns
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function fetchSearchableColumns(string $columnName, array $columnData)
    {
        if (isset($columnData['search'])) {

            if ($columnData['search']['enabled'] === true) {
                $this->searchableColumns[] = Str::lower($columnName);
                return ['searchable' => $this->searchableColumns];
            }
        }
        return ['searchable' => []];
    }

    /**
     * Extract a filter for the column
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     * @throws \Exception
     */
    public function fetchColumnFilter(string $columnName, array $columnData)
    {
        $filter = null;
        if (isset($columnData['filter'])) {
            // a column can only have one filter
            $filter = $this->pushFilter($columnName, $columnData['filter']);
        }
        return compact('filter');
    }

    /**
     * Extract display data for the column
     * This will be looped over so that it can be rendered as the table rows
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function fetchColumnData(string $columnName, array $columnData)
    {
        if (isset($columnData['data'])) {
            // note that this can also be a callback
            // the callback should take 2 args - $item (whats being iterated upon) and $key (the column name)
            // as such it would be called on the grid view
            $data = $columnData['data'];
        } else {
            // check for a presenter
            if (isset($columnData['presenter'])) {

                if (is_callable($columnData['presenter'])) {
                    // custom presenter
                    $data = function ($item, $row) use ($columnData) {
                        return call_user_func($columnData['presenter'], $item, $row);
                    };
                } else {
                    // Attempt to use the laracasts presenter. call the function on the model instance
                    // https://github.com/laracasts/Presenter
                    $data = function ($item, $row) use ($columnData) {
                        return $item->present()->{$columnData['presenter']};
                    };
                }
            } else {
                // check for dates
                if (isset($columnData['date'])) {
                    $data = function ($item, $row) use ($columnData) {
                        return Carbon::parse($item->{$row})->format($columnData['dateFormat'] ?? 'Y-m-d');
                    };
                } else {
                    // default processing strategy. Just access the attribute on the eloquent instance
                    $data = function ($item, $row) {
                        return $item->{$row};
                    };
                }
            }
        }
        return compact('data');
    }
}
