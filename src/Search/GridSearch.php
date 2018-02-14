<?php

namespace Leantony\Grid;

trait GridSearch
{
    /**
     * Check if a column can be searched
     *
     * @param string $columnName
     * @param array $columnData
     * @return bool
     */
    public function canSearchColumn(string $columnName, array $columnData)
    {
        return isset($columnData['search']) && $columnData['search']['enabled'] ?? false;
    }

    /**
     * Get the search operator
     *
     * @param string $columnName
     * @param array $columnData
     * @return array
     */
    public function fetchSearchOperator(string $columnName, array $columnData)
    {
        $operator = $columnData['search']['operator'] ?? 'like';
        return compact('operator');
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
        if ($userInput === null || empty(trim($userInput))) {
            return false;
        }
        return true;
    }

    /**
     * Search the columns
     *
     * @param string $columnName
     * @param array $columnData
     * @param string $operator
     * @param string $userInput
     * @return void
     */
    public function doSearch(string $columnName, array $columnData, string $operator, string $userInput)
    {
        $search = $columnData['search'] ?? [];
        $filter = $columnData['filter'] ?? [];
        // try using a custom search function if defined
        if (isset($search['query']) && is_callable($search['query'])) {
            call_user_func($search['query'], $this->query, $columnName, $userInput);

        } else {
            if (isset($filter['query']) && is_callable($search['query'])) {
                // otherwise, use the filter, if defined
                call_user_func($filter['query'], $this->query, $columnName, $userInput);
            } else {
                $value = $operator == 'like' ? '%' . $userInput . '%' : $userInput;

                $this->getQuery()->where($columnName, $operator, $value, $this->searchType);
            }
        }
    }
}