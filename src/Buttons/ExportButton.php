<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Buttons;

use Leantony\Grid\HasGridConfigurations;

class ExportButton extends GenericButton
{
    use HasGridConfigurations;

    public $position = 3;

    /**
     * Route to be used for export. This needs to be set when configuring the routes.
     *
     * @var string
     */
    protected $exportRoute = null;

    /**
     * Allow extra parameters to be added on this object
     *
     * @return array
     */
    public function getExtraParams()
    {
        $availableExportOptions = $this->getGridExportTypes();

        $createdOptions = [
            'xlsx' => [
                'name' => 'excel',
                'url' => $this->generateExportUrl('xlsx'),
                'icon' => 'file-excel-o',
                'title' => 'export to excel'
            ],
            'csv' => [
                'name' => 'csv',
                'url' => $this->generateExportUrl('csv'),
                'icon' => 'file',
                'title' => 'export to csv'
            ],
            'pdf' => [
                'name' => 'pdf',
                'url' => $this->generateExportUrl('pdf'),
                'icon' => 'file-pdf-o',
                'title' => 'export to pdf'
            ],
            'html' => [
                'name' => 'html',
                'url' => $this->generateExportUrl('html'),
                'icon' => 'html5',
                'title' => 'export to html'
            ],
            'json' => [
                'name' => 'json',
                'url' => $this->generateExportUrl('json'),
                'icon' => 'file-o',
                'title' => 'export to json'
            ]
        ];

        $exportOptions = collect($createdOptions)->reject(function ($option, $key) use ($availableExportOptions) {
            return !in_array($key, $availableExportOptions);
        })->toArray();

        return compact('exportOptions');
    }

    /**
     * Generate url from request or route name
     *
     * @param string $extension
     * @return string
     */
    protected function generateExportUrl($extension = 'xlsx'): string
    {
        if (filter_var($this->exportRoute, FILTER_VALIDATE_URL)) {
            $v = add_query_param(['export' => $extension]);
            // append the query string
            return request()->fullUrlWithQuery($v);
        }
        return route($this->exportRoute, add_query_param(['export' => $extension]));
    }
}