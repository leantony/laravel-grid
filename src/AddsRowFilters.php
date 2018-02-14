<?php

namespace Leantony\Grid;

use Illuminate\Support\Collection;
use Leantony\Grid\Filters\GenericFilter;

trait AddsRowFilters
{
    /**
     * Add a filter to the row
     *
     * @param array $rowValue params to be used for filtering
     * @param string $rowKey the column to be filtered
     * @return GenericFilter
     */
    public function pushFilter($rowValue, $rowKey): GenericFilter
    {
        $filter = $rowValue['filter'];
        $filterClass = $rowValue['filterClass'] ?? null;
        $filterDataAttributes = $rowValue['filterDataAttributes'] ?? [];
        $filterInstance = null;
        if (!$filter instanceof GenericFilter) {
            switch ($filter) {
                case 'date':
                    $filterInstance = $this->addDateFilter($rowKey, $filterDataAttributes, $filterClass);
                    break;
                case 'daterange':
                    $filterInstance = $this->addTextFilter($rowKey, $filterClass . ' date-range');
                    break;
                case 'text':
                    $filterInstance = $this->addTextFilter($rowKey, $filterClass);
                    break;
                case 'select':
                    $filterInstance = $this->addSelectFilter($rowKey, $rowValue['filterData'] ?? []);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown filter type " . $filter);
            }

        }
        return $filterInstance;
    }

    /**
     * Add a date picker filter
     *
     * @param string $id the id of the html element
     * @param string|null $elementClass the css class string that will be applied to the element
     * @param array $filterDataAttributes
     * @return GenericFilter
     */
    protected function addDateFilter($id, array $filterDataAttributes, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $id,
            'id' => $id,
            'formId' => $this->getFilterFormId(),
            'class' => 'form-control datepicker grid-filter ' . $elementClass,
            'type' => 'text', // just use text, since its text input
            'title' => 'filter by ' . $id,
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
     * @param string $id id of the html element
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     */
    protected function addTextFilter($id, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $id,
            'id' => $id,
            'formId' => $this->getFilterFormId(),
            'class' => 'form-control grid-filter ' . $elementClass,
            'type' => 'text',
            'title' => 'filter by ' . $id,
        ]);
        return $filter;
    }

    /**
     * Add a select filter to the row
     *
     * @param string $id id of the html element
     * @param array|Collection $data the data to be displayed on the dropdown
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     */
    protected function addSelectFilter($id, $data, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $id,
            'id' => $id,
            'formId' => $this->getFilterFormId(),
            'type' => 'select',
            'class' => 'form-control grid-filter' . $elementClass,
            'data' => $data,
        ]);
        return $filter;
    }
}