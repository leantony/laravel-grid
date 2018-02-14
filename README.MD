# Laravel grid (laravel 5.5)
This package allows rendering of data via a tabular format (grid). 
The grid uses bootstrap classes to style the `table` elements. Icons used are from `font-awesome`

## Requirements
The grid is [pjax](https://github.com/defunkt/jquery-pjax) ready out of the box. You can use this package - [spatie/laravel-pjax](https://github.com/spatie/laravel-pjax), or create the middleware on your own using [jeffrey way's middleware](https://gist.github.com/JeffreyWay/8526696b6f29201c4e33)

+ Bootstrap 3 (for the styling)
+ Font awesome (for icons)
+ Jquery (for the grid.js)
+ Jquery pjax (quickly view data without having to reload the page)
+ [Block UI](https://github.com/malsup/blockui) (optional)
+ [Date picker](https://github.com/uxsolutions/bootstrap-datepicker.git) (optional)

## Installation
The package is available on composer. Just run;
```bash
composer install leantony/laravel-grid
```
If you are using `laravel <5.5`, then ensure that you add the service provider manually to your `app.php` config file

## Usage
A laravel command is available to make it easy to create grids. 
Once installed, just run the command:
```bash
php artisan make:grid --model={modelClass}
```

Just make sure you replace `modelClass` with your actual `eloquent` model class.
Once this happens, a grid will be generated. Default namespace for grid generation is `App\Grids`.

Once the generation of the grid is done, you can add use it in your controller like this;
```php
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param UsersGridInterface $usersGrid
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(UsersGridInterface $usersGrid, Request $request)
    {
        // the 'query' argument needs to be an instance of the eloquent query builder
        $grid = $usersGrid->create(['query' => User::query(), 'request' => $request]);

        return view('welcome', ['grid' => $grid]);
    }
}
```
> If you inject the interface on the controller, just make sure that you add a binding to the service provider. Like so;
```php
/**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UsersGridInterface::class, UsersGrid::class);
    }
```


Ensure that your view/layout looks somthing like [this](https://gist.github.com/leantony/4a7a795904c60b6b7f91431e4241e37b). Especially with the javascript dependencies. The example is based off the grids sample application, which can be found [here](https://github.com/leantony/laravel_grid_app). The modal and its included view are not really required, so feel free to leave them out.
> The grid injects its javascript into the `@stack('scripts')` section, so please ensure that you have this

Then display the grid on your view like this;
```php
{!! $grid !!}
```

You should see something like this...
![sample](docs/sample.png)

# Customization
Most of the grid's functionality is customizable, and for that which is not, am working on it. However, you should be able to;
+ Add/Remove existing buttons
+ Prevent buttons from appearing on the grid
+ Altering css styles for the rows, columns and buttons on the grid
+ Make row entries clickable (navigating to another page)
+ Adding your own buttons
+ Changing the position where your buttons appear
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
