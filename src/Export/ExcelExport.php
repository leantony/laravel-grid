<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Export;

use Excel;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExcelExport implements FromQuery, WithTitle, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @var Builder
     */
    private $query;

    /**
     * @var array
     */
    private $pinch;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var string
     */
    private $title;
    /**
     * @var callable
     */
    private $mapperFunction;

    /**
     * @var array
     */
    private $headings;

    /**
     * ExcelExport constructor.
     * @param Builder $builder
     * @param array $pinch
     * @param array $columns
     * @param array $headings
     * @param string $title
     * @param callable $mapperFunction
     */
    public function __construct($builder, array $pinch, array $columns, array $headings, string $title, callable $mapperFunction)
    {
        $this->query = $builder;
        $this->pinch = $pinch;
        $this->columns = $columns;
        $this->headings = $headings;
        $this->title = $title;
        $this->mapperFunction = $mapperFunction;
    }

    public function query()
    {
        return $this->query->select($this->pinch);
    }

    public function map($data): array
    {
        return call_user_func($this->mapperFunction, $data, $this->columns, false);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }
}