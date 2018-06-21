<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

trait RendersGrid
{
    /**
     * Specify if the footer section needs to be displayed
     *
     * @var bool
     */
    protected $showFooter = false;

    /**
     * Controls rendering or not, for the search form
     *
     * @var bool
     */
    protected $shouldRenderSearchForm = true;

    /**
     * Controls rendering or not, for the grid filters, regardless of what is set on the columns
     *
     * @var bool
     */
    protected $shouldRenderFilters = true;

    /**
     * Controls if the grid needs to show a modal form when the rows are clicked
     * Has no effect if $linkableRows is set to false
     *
     * @var bool
     */
    protected $shouldShowModalOnClickingRow = false;

    /**
     * Controls the layout to use for rendering the grid.
     * If not set, defaults to the default view set on config
     *
     * @var null|string
     */
    protected $templateToUseForRendering = null;

    /**
     * If the grid should show a footer
     *
     * @return bool
     */
    public function shouldShowFooter(): bool
    {
        return $this->showFooter;
    }

    /**
     * @param bool $showFooter
     */
    public function setShowFooter(bool $showFooter): void
    {
        $this->showFooter = $showFooter;
    }

    /**
     * Define if the grid filters should be displayed for this grid
     *
     * @return bool
     */
    public function shouldRenderGridFilters(): bool
    {
        return $this->shouldRenderFilters;
    }

    /**
     * Grid should render search form
     *
     * @return bool
     */
    public function shouldRenderSearchForm(): bool
    {
        return $this->shouldRenderSearchForm;
    }

    /**
     * Define rendering of the search form
     *
     * @return $this
     */
    public function withoutSearchForm(): self
    {
        $this->shouldRenderSearchForm = false;
        return $this;
    }

    /**
     * Define a custom layout/template to use when rendering the grid
     *
     * @param string $layout
     * @return $this
     */
    public function withCustomTemplate(string $layout): self
    {
        $this->templateToUseForRendering = $layout;
        return $this;
    }

    /**
     * Define rendering of the filters
     *
     * @return $this
     */
    public function withoutFilters(): self
    {
        $this->shouldRenderFilters = false;
        return $this;
    }

    /**
     * Grid should show the modal form when the rows are clicked on
     * Works if the $linkableRows property is set to true
     *
     * @return bool
     */
    public function shouldShowModalOnClickingRow(): bool
    {
        return $this->shouldShowModalOnClickingRow;
    }

    /**
     * Get the rendering template/grid layout to use
     *
     * @return string
     */
    public function getRenderingTemplateToUse(): string
    {
        if ($this->templateToUseForRendering !== null && is_string($this->templateToUseForRendering)) {
            return $this->templateToUseForRendering;
        }
        return $this->getGridTemplateView();
    }

    /**
     * Render the grid title
     *
     * @return string
     */
    public function renderTitle(): string
    {
        return $this->getName();
    }

    /**
     * Specify the data to be sent to the view
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected function compactData($params = [])
    {
        $data = [
            'grid' => $this,
            'columns' => $this->getProcessedColumns()
        ];
        return array_merge($data, $this->getExtraParams($params));
    }

    /**
     * Any extra parameters that need to be passed to the grid
     * $params is func_get_args() passed from render
     *
     * @param array $params
     * @return array
     */
    public function getExtraParams($params)
    {
        return array_merge($this->extraParams, $params);
    }

    /**
     * Render the grid as HTML on the user defined view
     *
     * @return string
     * @throws \Throwable
     */
    public function render()
    {
        return view($this->getGridView(), $this->compactData(func_get_args()))->render();
    }

    /**
     * Pass the grid on to the user defined view e.g an index page, along with any data that may be required
     * Will dynamically switch between displaying the grid and downloading exported files
     *
     * @param string $viewName the view name
     * @param array $data any extra data to be sent to the view
     * @param string $as the variable to be sent to the view, representing the grid
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     * @throws \Throwable
     */
    public function renderOn(string $viewName, $data = [], $as = 'grid')
    {
        if ($this->getRequest()->has($this->getGridExportParam())) {
            return $this->exportHandler->export();
        }
        return view($viewName, array_merge($data, [$as => $this]));
    }

    /**
     * Render pagination info at the header of the user defined view
     *
     * @return string
     * @throws \Throwable
     */
    public function renderPaginationInfoAtHeader()
    {
        return view('leantony::grid.pagination.pagination-info', [
            'grid' => $this,
            'direction' => 'right'
        ])->render();
    }

    /**
     * Render pagination info at the footer of the user defined view
     *
     * @return string
     * @throws \Throwable
     */
    public function renderPaginationInfoAtFooter()
    {
        return view('leantony::grid.pagination.pagination-info', [
            'grid' => $this,
            'direction' => 'left',
            'atFooter' => true
        ])->render();
    }

    /**
     * Render pagination links at the footer of the user defined view
     *
     * @return string
     * @throws \Throwable
     */
    public function renderPaginationLinksSection()
    {
        return view('leantony::grid.pagination.pagination-links', [
            'grid' => $this,
        ])->render();
    }

    /**
     * Render grid filter
     *
     * @return string
     * @throws \Throwable
     */
    public function renderGridFilters()
    {
        return view('leantony::grid.filter', [
            'grid' => $this,
            'columns' => $this->getProcessedColumns(),
            'formId' => $this->getFilterFormId()
        ])->render();
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
            'colSize' => $this->getGridToolbarSize()[0], // size
            'action' => $this->getSearchUrl(),
            'id' => $this->getSearchFormId(),
            'name' => $this->getGridSearchParam(),
            'dataAttributes' => [],
            'placeholder' => $this->getSearchPlaceholder(),
        ];

        return view($this->getGridSearchView(), array_merge($data, $params))->render();
    }
}