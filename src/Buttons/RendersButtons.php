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
     * Buttons on the rows of the grid
     *
     * @var string
     */
    protected static $TYPE_ROW = 'rows';

    /**
     * Buttons on the toolbar of the grid
     *
     * @var string
     */
    protected static $TYPE_TOOLBAR = 'toolbar';

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
     */
    public function setDefaultButtons()
    {
        $this->buttons = [
            // toolbar buttons
            static::$TYPE_TOOLBAR => [
                'create' => $this->addCreateButton(),
                'refresh' => $this->addRefreshButton(),
                'export' => $this->addExportButton(),
            ],
            // row buttons
            static::$TYPE_ROW => [
                'view' => $this->addViewButton(),
                'delete' => $this->addDeleteButton()
            ]
        ];
    }

    /**
     * Add a create button to the grid
     *
     * @return GenericButton
     */
    protected function addCreateButton(): GenericButton
    {
        return (new GenericButton([
            'gridId' => $this->getId(),
            'position' => 1,
            'name' => "Create",
            'class' => "btn btn-success",
            'showModal' => true,
            'type' => static::$TYPE_TOOLBAR,
            'icon' => 'fa-plus-circle',
            'url' => $this->getCreateUrl(['ref' => $this->getId()]),
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
     */
    protected function addRefreshButton(): GenericButton
    {
        return (new GenericButton([
            'name' => 'Refresh',
            'pjaxEnabled' => true,
            'position' => 2,
            'icon' => 'fa-refresh',
            'class' => 'btn btn-primary',
            'gridId' => $this->getId(),
            'url' => $this->getRefreshUrl(),
            'type' => static::$TYPE_TOOLBAR,
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
     */
    protected function addExportButton(): GenericButton
    {
        return (new ExportButton([
            'name' => 'Export',
            'icon' => 'fa-download',
            'class' => 'btn btn-secondary',
            'title' => 'export data',
            'renderCustom' => function ($data) {
                return view('leantony::grid.buttons.export', $data)->render();
            },
            'gridId' => $this->getId(),
            'type' => static::$TYPE_TOOLBAR,
            'exportRoute' => $this->getIndexRouteName(),
            'renderIf' => function () {
                // only render the export button if `$allowsExporting` is set to true
                return in_array('export', $this->buttonsToGenerate);
            }
        ]));
    }

    /**
     * Add a view button to the grid
     *
     * @return GenericButton
     */
    protected function addViewButton(): GenericButton
    {
        return (new GenericButton([
            'name' => 'View',
            'icon' => 'fa-eye',
            'position' => 1,
            'class' => 'btn btn-outline-primary btn-sm grid-row-button',
            'showModal' => true,
            'gridId' => $this->getId(),
            'type' => static::$TYPE_ROW,
            'title' => 'view record',
            'url' => function ($gridName, $item) {
                return $this->getViewUrl([
                    $gridName => $item->{$this->getDefaultRouteParameter()}, 'ref' => $this->getId()
                ]);
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
     */
    protected function addDeleteButton(): GenericButton
    {
        return (new GenericButton([
            'gridId' => $this->getId(),
            'name' => 'Delete',
            'position' => 2,
            'class' => 'data-remote grid-row-button btn btn-outline-danger btn-sm',
            'icon' => 'fa-trash',
            'type' => static::$TYPE_ROW,
            'title' => 'delete record',
            'pjaxEnabled' => false,
            'dataAttributes' => [
                'method' => 'DELETE',
                'trigger-confirm' => true,
                'pjax-target' => '#' . $this->getId()
            ],
            'url' => function ($gridName, $item) {
                return route($this->getDeleteRouteName(), [
                    $gridName => $item->{$this->getDefaultRouteParameter()}, 'ref' => $this->getId()
                ]);
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
     * Add a custom button to the grid
     *
     * @param array $properties an array of key value pairs representing property names and values for the GenericButton instance
     * @param string|null $position where this button will be placed. Defaults to 'toolbar'
     * @return GenericButton
     * @throws \Exception
     */
    protected function makeCustomButton(array $properties, $position = null): GenericButton
    {
        $key = $properties['name'] ?? 'unknown';
        $position = $position ?? static::$TYPE_TOOLBAR;
        if ($position === static::$TYPE_TOOLBAR) {
            $this->addToolbarButton($key, new GenericButton(array_merge($properties, ['type' => $position])));
        } else {
            $this->addRowButton($key, new GenericButton(array_merge($properties, ['type' => $position])));
        }
        return $this->buttons[$position][$key];
    }

    /**
     * Add a custom button key to the array
     *
     * @param string $name
     * @return string
     */
    protected function makeButtonKey(string $name)
    {
        return str_slug($name);
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
        $this->addButton(static::$TYPE_TOOLBAR, $button, $instance);
    }

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
        $targets = [
            static::$TYPE_TOOLBAR,
            static::$TYPE_ROW,
        ];

        if (!in_array($target, $targets)) {
            throw new InvalidArgumentException(sprintf("Invalid target supplied. Expects value in array => %s", json_encode($targets)));
        }
        $this->buttons = array_merge_recursive($this->buttons, [
            $target => [
                $this->makeButtonKey($button) => $instance,
            ]
        ]);
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
        $this->addButton(static::$TYPE_ROW, $button, $instance);
    }

    /**
     * Edit an existing button
     *
     * @param string $target location where the button will be placed on the grid. Needs to be among the `$buttonTargets`
     * @param string $buttonName button name. Needs to be a button that exists among those that need to be generated
     * @param array $properties the key value pairs of the properties that need to be customized
     * @return void
     */
    protected function editButtonProperties($target, $buttonName, array $properties)
    {
        $instance = $this->buttons[$target][$this->makeButtonKey($buttonName)];

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
        $this->editButtonProperties(static::$TYPE_ROW, $button, $properties);
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
        $this->editButtonProperties(static::$TYPE_TOOLBAR, $button, $properties);
    }
}
