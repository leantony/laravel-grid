<?php

namespace Leantony\Grid\Buttons;

class ExportButton extends GenericButton
{
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
        return [
            'exportOptions' => [
                'excel' => [
                    'url' => $this->generateExportUrl('xlsx'),
                    'icon' => 'file-excel-o',
                    'title' => 'export to excel'
                ],
                'csv' => [
                    'url' => $this->generateExportUrl('csv'),
                    'icon' => 'file',
                    'title' => 'export to csv'
                ],
                'pdf' => [
                    'url' => $this->generateExportUrl('pdf'),
                    'icon' => 'file-pdf-o',
                    'title' => 'export to pdf'
                ]
            ]
        ];
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