<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Support\Str;

trait SearchesData
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

        // try to use the filter query, if allowed to
        if ($search['useFilterQuery'] ?? false) {

            if (isset($filter['query']) && is_callable($filter['query'])) {
                // otherwise, use the filter, if defined
                call_user_func($filter['query'], $this->getQuery(), $columnName, $userInput);
            }

        } else {

            if (isset($search['query']) && is_callable($search['query'])) {
                // use the search filter
                call_user_func($search['query'], $this->getQuery(), $columnName, $userInput);

            } else {

                if ($operator === strtolower('like')) {
                    $value = '%' . $userInput . '%';
                } else {
                    $value = $userInput;
                }

                $this->getQuery()->where($columnName, $operator, $value, $this->searchType);
            }
        }
    }

    /**
     * Render the search form on the grid
     *
     * @return string
     * @throws \Throwable
     */
    public function renderSearchForm()
    {
        $params = func_get_args();
        $data = [
            'colSize' => $this->toolbarSize[0], // size
            'action' => $this->getSearchRoute(),
            'id' => $this->getSearchFormId(),
            'name' => $this->getSearchParam(),
            'dataAttributes' => [],
            'placeholder' => $this->getSearchPlaceholder(),
        ];

        return view($this->getSearchView(), array_merge($data, $params))->render();
    }

    /**
     * Get the form id used for search
     *
     * @return string
     */
    public function getSearchFormId(): string
    {
        return 'search' . '-' . $this->getId();
    }

    /**
     * Get the placeholder to use on the search form
     *
     * @return string
     */
    private function getSearchPlaceholder()
    {
        if (empty($this->searchableColumns)) {
            $placeholder = Str::plural(Str::slug($this->getName()));

            return sprintf('search %s ...', $placeholder);
        }

        $placeholder = collect($this->searchableColumns)->implode(',');

        return sprintf('search %s by their %s ...', Str::lower($this->getName()), $placeholder);
    }

}