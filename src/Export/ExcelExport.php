<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Export;

use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExcelExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * The data
     *
     * @var Collection
     */
    protected $data;

    /**
     * The title of the report
     *
     * @var string
     */
    private $title;

    /**
     * The columns to export
     *
     * @var array
     */
    private $columns;


    /**
     * The headings to export
     *
     * @var array
     */
    private $headings;

    /**
     * DefaultExport constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->title = $params['title'];
        $this->columns = $params['columns'];
        $this->data = $params['data'];
        $this->headings = $params['headings'];
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}