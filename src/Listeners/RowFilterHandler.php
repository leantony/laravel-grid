<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Illuminate\Http\Request;
use Leantony\Grid\GridInterface;
use Leantony\Grid\GridResources;

class RowFilterHandler
{
    use GridResources;

    /**
     * RowFilterHandler constructor.
     * @param GridInterface $grid
     * @param Request $request
     * @param $builder
     * @param $validTableColumns
     * @param $data
     */
    public function __construct(GridInterface $grid, Request $request, $builder, $validTableColumns, $data)
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->query = $builder;
        $this->validGridColumns = $validTableColumns;
        $this->args = $data;
    }

    /**
     * Filter the grid rows
     *
     * @return void
     */
    public function filterRows()
    {
        if (!empty($this->request->query())) {
            $columns = $this->getGrid()->getColumns();
            $tableColumns = $this->getValidGridColumns();

            foreach ($columns as $columnName => $columnData) {
                // skip rows that are not to be filtered
                if (!$this->canFilter($columnName, $columnData)) {
                    continue;
                }
                // user input check
                if (!$this->canUseProvidedUserInput($this->getRequest()->get($columnName))) {
                    continue;
                }
                // column check. Since the column data is coming from a user query
                if (!$this->canUseProvidedColumn($columnName, $tableColumns)) {
                    continue;
                }
                $operator = $this->extractFilterOperator($columnName, $columnData)['operator'];

                $this->doFilter($columnName, $columnData, $operator, $this->getRequest()->get($columnName));
            }
        }
    }

    /**
     * Check if filtering can be done
     *
     * @param string $columnName
     * @param array $columnData
     * @return bool
     */
    public function canFilter(string $columnName, array $columnData)
    {
        return isset($columnData['filter']) && $columnData['filter']['enabled'] ?? false;
    }

    /**
     * Check if provided user input can be used
     *
     * @param string|null $userInput
     * @return bool
     */
    public function canUseProvidedUserInput($userInput)
    {
        // skip empty requests
        if ($userInput === null || strlen(trim($userInput)) < 1) {
            return false;
        }
        return true;
    }

    /**
     * Check if the provided column can be used
     *
     * @param $columnName
     * @param $validColumns
     * @return bool
     */
    public function canUseProvidedColumn(string $columnName, array $validColumns)
    {
        return in_array($columnName, $validColumns);
    }

    /**
     * Extract filter operator
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function extractFilterOperator(string $columnName, array $columnData)
    {
        $operator = $columnData['filter']['operator'] ?? '=';
        return compact('operator');
    }

    /**
     * Filter the data
     *
     * @param string $columnName
     * @param array $columnData
     * @param string $operator
     * @param string $userInput
     * @return void
     */
    public function doFilter(string $columnName, array $columnData, string $operator, string $userInput)
    {
        $filter = $columnData['filter'] ?? [];
        $data = $columnData['data'] ?? [];
        // check for custom filter strategies and call them
        if (isset($filter['query']) && is_callable($filter['query'])) {
            call_user_func($filter['query'], $this->getQuery(), $columnName, $userInput);
        } else {

            if ($operator === strtolower('like')) {
                $value = '%' . $userInput . '%';
            } else {
                $value = $userInput;
            }

            if (isset($filter['type']) && ($filter['type'] === 'daterange' && $filter['enabled'] === true)) {
                // check for date range values
                $exploded = explode(' - ', $value, 2);
                if (count($exploded) > 1) {
                    // skip invalid dates
                    if (strtotime($exploded[0]) && strtotime($exploded[1])) {
                        $this->getQuery()->whereBetween($columnName, $exploded, $this->getGrid()->getGridFilterQueryType());
                    }
                } else {
                    // not a date range
                    // skip invalid dates
                    if (strtotime($value)) {
                        $this->getQuery()->whereDate($columnName, $operator, $value, $this->getGrid()->getGridFilterQueryType());
                    }
                }
            } else {
                $this->getQuery()->where($columnName, $operator, $value, $this->getGrid()->getGridFilterQueryType());
            }
        }
    }
}