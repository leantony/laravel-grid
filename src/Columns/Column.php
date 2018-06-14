<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Columns;

use InvalidArgumentException;
use Leantony\Grid\Filters\GenericFilter;

class Column
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $data;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $searchableColumns;

    /**
     * @var string
     */
    private $rowClass;

    /**
     * @var string
     */
    private $columnClass;

    /**
     * @var boolean
     */
    private $isSortable;

    /**
     * @var GenericFilter
     */
    private $filter;

    /**
     * @var boolean
     */
    private $useRawFormat;

    /**
     * @var boolean
     */
    private $isExportable;

    /**
     * @var array|mixed
     */
    private $extra;

    /**
     * @var array
     */
    private $footer;

    /**
     * Dynamically get properties
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new InvalidArgumentException("The property " . $name . " was not found on this class");
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @return mixed|array
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param mixed|array $footer
     * @return Column
     */
    public function setFooter($footer): Column
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     * @return Column
     */
    public function setExtra(array $extra): Column
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Column
     */
    public function setName(string $name): Column
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return callable
     */
    public function getData(): callable
    {
        return $this->data;
    }

    /**
     * @param callable $data
     * @return Column
     */
    public function setData(callable $data): Column
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Column
     */
    public function setKey(string $key): Column
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    /**
     * @param array $searchableColumns
     * @return Column
     */
    public function setSearchableColumns(array $searchableColumns): Column
    {
        $this->searchableColumns = $searchableColumns;
        return $this;
    }

    /**
     * @return string
     */
    public function getRowClass(): string
    {
        return $this->rowClass;
    }

    /**
     * @param string $rowClass
     * @return Column
     */
    public function setRowClass(string $rowClass): Column
    {
        $this->rowClass = $rowClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getColumnClass(): string
    {
        return $this->columnClass;
    }

    /**
     * @param string $columnClass
     * @return Column
     */
    public function setColumnClass(string $columnClass): Column
    {
        $this->columnClass = $columnClass;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    /**
     * @param bool $isSortable
     * @return Column
     */
    public function setIsSortable(bool $isSortable): Column
    {
        $this->isSortable = $isSortable;
        return $this;
    }

    /**
     * @return GenericFilter
     */
    public function getFilter(): GenericFilter
    {
        return $this->filter;
    }

    /**
     * @param GenericFilter $filter
     * @return Column
     */
    public function setFilter($filter): Column
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseRawFormat(): bool
    {
        return $this->useRawFormat;
    }

    /**
     * @param bool $useRawFormat
     * @return Column
     */
    public function setUseRawFormat(bool $useRawFormat): Column
    {
        $this->useRawFormat = $useRawFormat;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportable(): bool
    {
        return $this->isExportable;
    }

    /**
     * @param bool $isExportable
     * @return Column
     */
    public function setIsExportable(bool $isExportable): Column
    {
        $this->isExportable = $isExportable;
        return $this;
    }
}