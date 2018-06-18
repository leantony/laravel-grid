<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Support\Collection;
use InvalidArgumentException;

trait AddsColumnFilters
{
    /**
     * Add a filter to the column. It will be rendered just below the column name, as a type defined below
     *
     * @param string $columnName the column to be filtered
     * @param array $columnData params to be used for filtering
     * @return GenericFilter
     * @throws \Exception
     */
    public function pushFilter($columnName, $columnData): GenericFilter
    {
        $filterType = $columnData['type'] ?? 'text'; // default
        $filterClass = $columnData['class'] ?? null;
        $filterDataAttributes = $columnData['dataAttributes'] ?? [];
        $filterEnabled = $columnData['enabled'] ?? true;
        $filterInstance = null;
        if (!$filterType instanceof GenericFilter) {
            switch ($filterType) {
                case 'date':
                    {
                        $filterInstance = $this->addDateFilter(
                            $filterEnabled, $columnName, $filterDataAttributes, $filterClass
                        );
                        break;
                    }
                case 'daterange':
                    {
                        // uses https://github.com/dangrossman/bootstrap-daterangepicker.git
                        $filterInstance = $this->addTextFilter(
                            $filterEnabled, $columnName, $filterClass . ' date-range'
                        );
                        break;
                    }
                case 'text':
                    {
                        // use text for any other filter type. E.g a custom one you might need
                        $filterInstance = $this->addTextFilter(
                            $filterEnabled, $columnName, $filterClass
                        );
                        break;
                    }
                case 'select':
                    {
                        $filterInstance = $this->addSelectFilter(
                            $filterEnabled, $columnName, $columnData['data'] ?? []
                        );
                        break;
                    }
                default:
                    throw new InvalidArgumentException("Unknown filterType type " . $filterType . " for " . $columnName);
            }

        }
        return $filterInstance;
    }

    /**
     * Add a date picker filter. Uses https://github.com/dangrossman/bootstrap-daterangepicker.git
     *
     * @param $enabled
     * @param string $elementId the id of the html element
     * @param array $filterDataAttributes
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     * @throws \Exception
     */
    protected function addDateFilter(
        $enabled,
        $elementId,
        array $filterDataAttributes,
        $elementClass = null
    ): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $this->createElementId($elementId),
            'enabled' => $enabled,
            'formId' => $this->getFilterFormId(),
            'class' => 'form-control grid-datepicker grid-filter ' . $elementClass,
            'type' => 'text', // just use text, since its text input
            'title' => 'filter by ' . $elementId,
            'dataAttributes' => $filterDataAttributes
        ]);
        return $filter;
    }

    /**
     * Get id for the filter form
     *
     * @return string
     */
    public function getFilterFormId()
    {
        return $this->getId() . '-' . 'filter';
    }

    /**
     * Add a text filter to the data
     *
     * @param $enabled
     * @param string $elementId id of the html element
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     * @throws \Exception
     */
    protected function addTextFilter($enabled, $elementId, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $this->createElementId($elementId),
            'enabled' => $enabled,
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
     * @param $enabled
     * @param string $elementId id of the html element
     * @param array|Collection $data the data to be displayed on the dropdown
     * @param string|null $elementClass the css class string that will be applied to the element
     * @return GenericFilter
     * @throws \Exception
     */
    protected function addSelectFilter($enabled, $elementId, $data, $elementClass = null): GenericFilter
    {
        $filter = new GenericFilter([
            'name' => $elementId,
            'id' => $this->createElementId($elementId),
            'enabled' => $enabled,
            'formId' => $this->getFilterFormId(),
            'type' => 'select',
            'class' => 'form-control grid-filter' . $elementClass,
            'data' => $data,
        ]);
        return $filter;
    }

    /**
     * Create the ID for the grid filter
     *
     * @param $elementId
     * @return string
     */
    protected function createElementId($elementId): string
    {
        return 'grid-filter-' . $elementId;
    }
}