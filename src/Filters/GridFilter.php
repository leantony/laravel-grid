<?php

namespace Leantony\Grid;

trait GridFilter
{
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
     * Check if the provided column can be used
     *
     * @param $columnName
     * @param $validColumns
     * @return bool
     */
    public function canUseProvidedColumn(string $columnName, array $validColumns)
    {
        return !in_array($columnName, $validColumns);
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
            call_user_func($filter['query'], $this->query, $columnName, $userInput);
        } else {
            if ($operator == 'like') {
                $value = '%' . $userInput . '%';
            } else {
                $value = $columnName;
            }
            if (isset($data['date']) && $data['date'] === true) {
                // skip invalid dates
                if (strtotime($value)) {
                    $this->getQuery()->whereDate($columnName, $operator, $value, $this->filterType);
                }
            } else {
                $this->getQuery()->where($columnName, $operator, $value, $this->filterType);
            }
        }
    }
}