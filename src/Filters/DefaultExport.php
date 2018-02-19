<?php

namespace Leantony\Grid\Filters;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DefaultExport implements FromView, WithTitle, WithHeadings
{
    /**
     * The data
     *
     * @var Collection|array
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
     * DefaultExport constructor.
     * @param $title
     * @param $columns
     * @param $data
     */
    public function __construct($title, $columns, $data)
    {
        $this->title = $title;
        $this->columns = $columns;
        $this->data = $data;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('leantony::reports.report', [
            'columns' => $this->columns,
            'data' => $this->data,
        ]);
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
        return $this->columns;
    }
}