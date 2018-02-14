<?php

namespace Leantony\Grid;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait GridColumns
{

    /**
     * The columns that appear on the table headers. Specified as $key => $value
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The regxp pattern to be used to format the column names that will appear on the UI
     * All symbols and invalid characters would be ignored and replaced with a space
     *
     * @var string
     */
    protected $labelNamePattern = "/[^a-z0-9 -]+/";

    /**
     * Columns to be used as search
     *
     * @var array
     */
    protected $searchableColumns = [];

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
            if (!$func($columnData)) {
                return false;
            }
        }
        return true;
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
            $label = ucwords(preg_replace($this->labelNamePattern, ' ', $columnName));
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
            // as such it would be called on the grid view
            $data = $columnData['data'];
        } else {
            // check for a presenter
            if (isset($columnData['present'])) {
                if (is_callable($columnData['present'])) {
                    // custom
                    $data = function ($item, $row) use ($columnData) {
                        return call_user_func($columnData['present'], $item, $row);
                    };
                } else {
                    // laracasts presenter. call the function on the model instance
                    $data = function ($item, $row) use ($columnData) {
                        return $item->present()->{$columnData['present']};
                    };
                }
            } else {
                // format any dates
                if (isset($columnData['date'])) {
                    $data = function ($item, $row) use ($columnData) {
                        return Carbon::parse($item->{$row})->format($columnData['dateFormat'] ?? 'Y-m-d');
                    };
                } else {
                    $data = function ($item, $row) {
                        return $item->{$row};
                    };
                }
            }
        }
        return compact('data');
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
            $columnClass = $classAttributes['column'] ?? 'col-md-2';
            $rowClass = $classAttributes['row'] ?? '';
        } else {
            $columnClass = '';
            $rowClass = '';
        }

        return compact('columnClass', 'rowClass');
    }
}