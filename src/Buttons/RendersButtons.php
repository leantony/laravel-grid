<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Buttons;

use InvalidArgumentException;

trait RendersButtons
{
    /**
     * Quick toggle to define if we need to render any buttons on the grid
     *
     * @var bool
     */
    protected $renderButtons = true;

    /**
     * Valid positions in the grid where buttons can be placed
     *
     * @var array
     */
    protected $buttonTargets = ['rows', 'toolbar'];

    /**
     * List of default buttons to be generated on the grid. If you need to add custom buttons
     * use `addRowButton`, `addToolBarButton` or `addCustomButton`
     *
     * `create` - Displays a form/page/modal to create the entity
     * `view` - Displays a form/page/modal containing entity data
     * `delete` - Deletes the entity
     * `refresh` - Refreshes the grid
     * `export` - Exports the data as pdf, excel, word, or csv
     *
     * @var array
     */
    protected $buttonsToGenerate = [
        'create',
        'view',
        'delete',
        'refresh',
        'export'
    ];

    /**
     * Configure rendered buttons, if need be.
     * For example, within this function, you can call `addButton()` to add a button to the grid
     * You can also call `editButtonProperties()` to edit any properties for buttons that will be generated
     *
     * @return void
     */
    abstract public function configureButtons();

    /**
     * Add a button to the grid
     *
     * @param $target string the location where the button will be rendered. Needs to be among the `$buttonTargets`
     * @param $button string the button name. Can be any name
     * @param $instance GenericButton the button instance
     *
     * @return void
     */
    public function addButton(string $target, string $button, GenericButton $instance)
    {
        if (!in_array($target, $this->buttonTargets)) {
            throw new InvalidArgumentException("Invalid target supplied. Expects either of => " . json_encode($this->buttonTargets));
        }
        $this->buttons = array_merge_recursive($this->buttons, [
            $target => [
                $button => $instance,
            ]
        ]);
    }

    /**
     * Sets an array of buttons that would be rendered to the grid
     *
     * @return void
     * @throws \Exception
     */
    public function setButtons()
    {
        $this->setDefaultButtons();
    }

    /**
     * Set default buttons for the grid
     *
     * @return void
     * @throws \Exception
     */
    public function setDefaultButtons()
    {
        $this->buttons = [
            // toolbar buttons
            'toolbar' => [
                'create' => $this->addCreateButton(),
                'refresh' => $this->addRefreshButton(),
                'export' => $this->addExportButton(),
            ],
            // row buttons
            'rows' => [
                'view' => $this->addViewButton(),
                'delete' => $this->addDeleteButton()
            ]
        ];
    }

    /**
     * Add a create button to the grid
     *
     * @return GenericButton
     * @throws \Exception
     */
    protected function addCreateButton(): GenericButton
    {
        return (new GenericButton([
            'gridId' => $this->id,
            'position' => 1,
            'name' => "Create",
            'class' => "btn btn-success",
            'showModal' => true,
            'type' => 'toolbar',
            'icon' => 'fa-plus-circle',
            'url' => $this->getCreateRouteName(),
            'title' => 'add new ' . $this->shortSingularGridName(),
            'renderIf' => function () {
                return in_array('create', $this->buttonsToGenerate);
            }
        ]));
    }

    /**
     * Add a refresh button to the grid
     *
     * @return GenericButton
     * @throws \Exception
     */
    protected function addRefreshButton(): GenericButton
    {
        return (new GenericButton([
            'name' => 'Refresh',
            'pjaxEnabled' => true,
            'position' => 2,
            'icon' => 'fa-refresh',
            'gridId' => $this->id,
            'url' => $this->getIndexRouteLink(),
            'type' => 'toolbar',
            'title' => 'refresh table for ' . strtolower($this->name),
            'renderIf' => function () {
                return in_array('refresh', $this->buttonsToGenerate);
            }
        ]));
    }

    /**
     * Add an export button to the grid
     *
     * @return GenericButton
     * @throws \Exception
     */
    protected function addExportButton(): GenericButton
    {
        return (new ExportButton([
            'name' => 'Export',
            'icon' => 'fa-download',
            'class' => 'btn btn-default',
            'title' => 'export data',
            'renderCustom' => function ($data) {
                return view('leantony::grid.buttons.export', $data)->render();
            },
            'gridId' => $this->id,
            'type' => 'toolbar',
            'exportRoute' => $this->getIndexRouteLink(),
            'renderIf' => function () {
                // only render the export button if `$allowsExporting` is set to true
                return in_array('export', $this->buttonsToGenerate) || $this->allowsExporting;
            }
        ]));
    }

    /**
     * Add a view button to the grid
     *
     * @return GenericButton
     * @throws \Exception
     */
    protected function addViewButton(): GenericButton
    {
        return (new GenericButton([
            'name' => 'View',
            'icon' => 'fa-eye',
            'position' => 1,
            'class' => 'btn btn-primary btn-xs',
            'showModal' => true,
            'gridId' => $this->id,
            'type' => 'row',
            'title' => 'view record',
            'url' => function ($gridName, $item) {
                return route($this->viewRouteName, [$gridName => $item->id, 'ref' => $this->getId()]);
            },
            'renderIf' => function ($gridName, $item) {
                return in_array('view', $this->buttonsToGenerate);
            }
        ]));
    }

    /**
     * Add a delete button to the grid
     *
     * @return GenericButton
     * @throws \Exception
     */
    protected function addDeleteButton(): GenericButton
    {
        return (new GenericButton([
            'gridId' => $this->id,
            'name' => 'Delete',
            'position' => 2,
            'class' => 'data-remote btn btn-danger btn-xs btn-grid-row',
            'icon' => 'fa-trash',
            'type' => 'row',
            'title' => 'delete record',
            'pjaxEnabled' => false,
            'dataAttributes' => [
                'method' => 'DELETE',
                'confirm' => 'Sure?',
                'pjax-target' => '#' . $this->id
            ],
            'url' => function ($gridName, $item) {
                return route($this->viewRouteName, [$gridName => $item->id, 'ref' => $this->getId()]);
            },
            'renderIf' => function ($gridName, $item) {
                return in_array('delete', $this->buttonsToGenerate);
            }
        ]));
    }

    /**
     * Get an array of button instances to be rendered on the grid
     *
     * @param string $section
     * @return array
     */
    public function getButtons($section = 'toolbar')
    {
        $buttons = $section ? $this->buttons[$section] : $this->buttons;
        // sort the buttons by position
        return collect($buttons)->sortBy(function ($v) {
            return $v->position;
        })->toArray();
    }

    /**
     * Clear the buttons on a specific section
     *
     * @param string $section
     * @return void
     */
    protected function clearButtons(string $section = 'toolbar')
    {
        $this->buttons[$section] = [];
    }

    /**
     * Clear all buttons
     *
     * @return void
     */
    protected function clearAllButtons()
    {
        $this->buttons = [];
    }

    /**
     * Check if the grid has any buttons
     *
     * @param string $section
     * @return bool
     */
    public function hasButtons(string $section = 'toolbar')
    {
        if (!$this->renderButtons) {
            // rendering disabled
            return false;
        }
        // no buttons on section
        return count(array_get($this->buttons, $section, [])) === 0 ? false : true;
    }

    /**
     * Return the view used to display the search form
     *
     * @return string
     */
    public function getSearchView(): string
    {
        return 'leantony::grid.search';
    }

    /**
     * Add a custom button to the grid
     *
     * @param array $properties an array of key value pairs representing property names and values for the GenericButton instance
     * @param string|null $position where this button will be placed. Defaults to 'toolbar'
     * @return GenericButton
     * @throws \Exception
     */
    protected function makeCustomButton(array $properties, $position = null): GenericButton
    {
        $name = $properties['name'] ?? 'unknown';
        $position = $position ?? 'toolbar';
        if ($position === 'toolbar') {
            $this->addToolbarButton($name, new GenericButton(array_merge($properties, ['type' => $position])));
        } else {
            $this->addRowButton($name, new GenericButton(array_merge($properties, ['type' => $position])));
        }
        return $this->buttons[$position][$name];
    }

    /**
     * Add a button on the grid toolbar section
     *
     * @param $button string button name
     * @param $instance GenericButton button instance
     *
     * @return void
     */
    protected function addToolbarButton(string $button, GenericButton $instance)
    {
        $this->addButton('toolbar', strtolower($button), $instance);
    }

    /**
     * Add a button on the grid rows section
     *
     * @param $button string the button name
     * @param $instance GenericButton the button instance
     *
     * @return void
     */
    protected function addRowButton(string $button, GenericButton $instance)
    {
        $this->addButton('rows', strtolower($button), $instance);
    }

    /**
     * Edit an existing button
     *
     * @param string $target location where the button will be placed on the grid. Needs to be among the `$buttonTargets`
     * @param string $button button name. Needs to be a button that exists among those that need to be generated
     * @param array $properties the key value pairs of the properties that need to be customized
     * @return void
     */
    protected function editButtonProperties($target, $button, array $properties)
    {
        $instance = $this->buttons[$target][$button];

        $this->ensureButtonInstanceValidity($instance);

        foreach ($properties as $k => $v) {
            $instance->{$k} = $v;
        }
    }

    /**
     * Check button availability and validity
     *
     * @param $button
     */
    private function ensureButtonInstanceValidity($button)
    {
        if ($button === null || !$button instanceof GenericButton) {
            throw new InvalidArgumentException(sprintf("The button %s could not be found or is invalid.", $button));
        }
    }

    /**
     * Edit an existing button on the grid rows
     *
     * @param string $button button name. Needs to be a button that exists among those that need to be generated
     * @param array $properties the key value pairs of the properties that need to be customized
     * @return void
     */
    protected function editRowButton(string $button, array $properties)
    {
        $instance = $this->buttons['rows'][$button];

        $this->ensureButtonInstanceValidity($instance);

        foreach ($properties as $k => $v) {
            $instance->{$k} = $v;
        }
    }

    /**
     * Edit an existing button on the grid toolbar
     *
     * @param string $button button name. Needs to be a button that exists among those that need to be generated
     * @param array $properties the key value pairs of the properties that need to be customized
     * @return void
     */
    protected function editToolbarButton(string $button, array $properties)
    {
        $instance = $this->buttons['toolbar'][$button];

        $this->ensureButtonInstanceValidity($instance);

        foreach ($properties as $k => $v) {
            $instance->{$k} = $v;
        }
    }
}