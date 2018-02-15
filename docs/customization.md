# Customization
Most of the grid's functionality is customizable, and for that which is not, am working on it. However, you should be able to;
+ [Customize existing buttons](customize_buttons.md)
+ Make row entries clickable (navigating to another page)
+ Enable/disable sort functionality per column
+ Add/Remove filters per column
+ Customize how each column on the grid is filtered
+ Customize how each of the search enabled columns is searched
+ Enable/disable export to pdf, excel and csv
+ Use the functionality provided for dynamic modal forms to rapidly create CRUD applications (coming soon)

We will go through each of these one by one, with examples on how to do each. Before that though, this is how a generated grid looks like;
```php
<?php

namespace App\Grids;

use Closure;
use Leantony\Grid\Grid;

class UsersGrid extends Grid implements UsersGridInterface
{
    /**
     * The name of the grid
     *
     * @var string
     */
    protected $name = 'Users';

    /**
     * List of buttons to be generated on the grid
     *
     * @var array
     */
    protected $buttonsToGenerate = [
        'create', 'view', 'delete', 'refresh', 'export'
    ];

    /**
     * Specify if the rows on the table should be clicked to navigate to the record
     *
     * @var bool
     */
    protected $linkableRows = false;

    /**
    * Set the columns to be displayed. Check `docs/columns.md` for more information
    *
    * @return void
    * @throws \Exception if an error occurs during parsing of row data
    */
    public function setColumns()
    {
        $this->columns = [
		    "id" => [
		        "label" => "ID",
		        "filter" => [
		            "enabled" => true,
		            "operator" => "="
		        ],
		        "styles" => [
		            "column" => "col-md-2"
		        ]
		    ],
		    "name" => [
		        "search" => [
		            "enabled" => true
		        ]
		    ],
		    "email" => [
		        "search" => [
		            "enabled" => true
		        ]
		    ],
		    "created_at" => [
		        "sort" => false,
		        "date" => "true",
		        "filter" => [
		            "enabled" => true,
		            "type" => "date",
		            "operator" => "<="
		        ]
		    ]
		];
    }

    /**
     * Set the links/routes. This are referenced using named routes, for the sake of simplicity
     *
     * @return void
     */
    public function setRoutes()
    {
        // searching, sorting and filtering
        $this->sortRouteName = 'users.index';
        $this->searchRoute = 'users.index';

        // crud support
        $this->indexRouteName = 'users.index';
        $this->createRouteName = 'users.create';
        $this->viewRouteName = 'users.show';
        $this->deleteRouteName = 'users.destroy';
    }

    /**
    * Return a closure that is executed per row, to render a link that will be clicked on to execute an action
    *
    * @return Closure
    */
    public function getLinkableCallback(): Closure
    {
        $view = $this->viewRouteName;

        return function ($gridName, $item) use ($view) {
            return route($view, [$gridName => $item->id]);
        };
    }

    /**
    * Configure rendered buttons, or add your own
    *
    * @return void
    */
    public function configureButtons()
    {
        //
    }

    /**
    * Returns a closure that will be executed to apply a class for each row on the grid
    * The closure takes two arguments - `name` of grid, and `item` being iterated upon
    *
    * @return Closure
    */
    public function getRowCssStyle(): Closure
    {
        return function ($gridName, $item) {
            return "";
        };
    }
}
```

