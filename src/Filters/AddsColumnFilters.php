<?php

namespace Leantony\Grid;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Leantony\Grid\Filters\GenericFilter;

trait AddsColumnFilters
{
    /**
     * Add a filter to the column. It will be rendered just below the column name, as a type defined below
     *
     * @param string $columnName the column to be filtered
     * @param array $columnData params to be used for filtering
     * @return GenericFilter
     */
    public function pushFilter($columnName, $columnData): GenericFilter
    {
        $filterType = $columnData['type'] ?? 'text'; // default
        $filterClass = $columnData['class'] ?? null;
        $filterDataAttributes = $columnData['dataAttributes'] ?? [];
        $filterInstance = null;
        if (!$filterType instanceof GenericFilter) {
            switch ($filterType) {
                case 'date':
                    $filterInstance = $this->addDateFilter($columnName, $filterDataAttributes, $filterClass);
                    break;
                case 'daterange':
                    $filterInstance = $this->addTextFilter($columnName, $filterClass . ' date-range');
                    break;
                case 'text':
                    // use text for any other filter type. E.g a custom one you might need
                    $filterInstance = $this->addTextFilter($columnName, $filterClass);
                    break;
                case 'select':
                    $filterInstance = $this->addSelectFilter($columnName, $columnData['data'] ?? []);
                    break;
                default:
                    throw new InvalidArgumentException("Unknown filterType type " . $filterType . " for " . $columnName);
            }

        }
        return $filterInstance;
    }

    /**
     * Add a date picker filter. Uses https://github.com/uxsolutions/bootstrap-datepicker.git
     *
     * @param string $elementId the id of the html element
     * @param string|null $elementClass the css class string that will be applied to the element
     * @param array $filterDataAttributes
     * @return GenericFilter
     */
    protected function addDateFilter($elementId, array $filterDataAttributes, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $elementId,
            'formId' => $this->getFilterFormId(),
            'class' => 'form-control datepicker grid-filter ' . $elementClass,
            'type' => 'text', // just use text, since its text input
            'title' => 'filter by ' . $elementId,
            'dataAttributes' => [
                'date-format' => $filterDataAttributes['format'] ?? 'yyyy-mm-dd',
                'date-start-date' => $filterDataAttributes['start'] ?? '-100y',
                'date-end-date' => $filterDataAttributes['end'] ?? '+100y'
            ]
        ]);
        return $filter;
    }

    /**
     * Add a text filter to the data
     *
     * @param string $elementId id of the html element
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     */
    protected function addTextFilter($elementId, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $elementId,
            'formId' => $this->getFilterFormId(),
            'class' => 'form-control grid-filter ' . $elementClass,
            'type' => 'text',
            'title' => 'filter by ' . $elementId,
        ]);
        return $filter;
    }

    /**
     * Add a select filter to the row
     *
     * @param string $elementId id of the html element
     * @param array|Collection $data the data to be displayed on the dropdown
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     */
    protected function addSelectFilter($elementId, $data, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $elementId,
            'formId' => $this->getFilterFormId(),
            'type' => 'select',
            'class' => 'form-control grid-filter' . $elementClass,
            'data' => $data,
        ]);
        return $filter;
    }
}