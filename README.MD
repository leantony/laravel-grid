# Laravel grid (laravel 5.6+)

[![Build Status](https://travis-ci.org/leantony/laravel-grid.svg?branch=master)](https://travis-ci.org/leantony/laravel-grid)

This package allows rendering of data via a tabular format (grid). The grid uses bootstrap classes to style the `table` elements. Icons used are from `font-awesome`, and most of the functionality is insipred by the yii2's framework's gridview widget. 

# Features
+ Grid generation via a laravel command
+ Full export functionality - (to pdf, csv, and excel. Relies on the `maatwebsite/excel` package)
+ Bootstrap modal support
+ Customizable column filters (supports text, date, date-range, dropdowns )
+ Adding of dynamic column names (that do not exist on the actual eloquent models)
+ Raw HTML support for the column data
+ Customizable global search
+ Attribute sorting
+ Dynamic addition, removal and disabling of buttons
+ Dynamic manipulation of row and column HTML classes
+ pjax support
+ Pagination


## Requirements
The grid supports [pjax](https://github.com/defunkt/jquery-pjax) out of the box. For pjax support on laravel, try - [spatie/laravel-pjax](https://github.com/spatie/laravel-pjax), or create the middleware on your own using [jeffrey way's middleware.](https://gist.github.com/JeffreyWay/8526696b6f29201c4e33)

+ Bootstrap 3 (for the styling)
+ Font awesome (for icons)
+ Jquery (required by bootstrap and the package's javascript)
+ Jquery pjax (quickly view data without having to reload the page)
+ [Block UI](https://github.com/malsup/blockui) (optional - to 'block' the UI when doing an AJAX request that might presumably take a while).
+ [Date picker](https://github.com/uxsolutions/bootstrap-datepicker.git) (optional - for the date filters)


## Installation
The package is available on packagist. Just run;
```bash
composer install leantony/laravel-grid
```
You can then publish the resources (views) and assets (css & js), incase you need to customize them. The css and js will be copied to `public/vendor/leantony/grid`

```bash
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider"
```
> You can publish the assets and views separately by passing the `--tag` argument to the command. For the argument values, try `assets`, `views` or `config` for js|css assets, views and config respectively.

Or just run `php artisan vendor:publish` and select the provider index by name `Leantony\Grid\Providers\ServiceProvider`

When the package is updated, it is highly likely that you will also need to update the javascript assets. To do that, run this command below after an update;
```bash
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider" --tag=assets --force
```
> You can also place this command in composer so that it is executed automatically on each update run. Like so;
```php
// ... composer config
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover",
    "@php artisan vendor:publish --provider=\"Leantony\\Grid\\Providers\\ServiceProvider\" --tag=assets --force"
]
```


## Usage
A laravel command is available to make it easy to create grids. 
Once installed, just run the command:
```bash
php artisan make:grid --model="{modelClass}"
```

Just make sure you replace `{modelClass}` with your actual `eloquent` model class. E.g 
```bash
php artisan make:grid --model="App\User"
```
Once this is run, a grid will be generated. Default namespace for grid generation is `App\Grids`.

Once the generation of the grid is done, you can add add it in your controller like this. E.g a `user` model grid:

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
        // you can load relationships at this point
        return $usersGrid
                    ->create(['query' => User::query(), 'request' => $request])
                    ->renderOn('welcome'); // render the grid on the welcome view
    }
}
```
> If you inject the interface on the controller, just make sure that you add a binding to the service provider. Like this;
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


Otherwise, you can also instantiate the grid class like any other class then inject any constructor dependencies you might need.
```php
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return (new UsersGrid())
                    ->create(['query' => User::query(), 'request' => $request, 'user' => $user]) // you can then add it as a public property on the grid with name $user, then use it
                    ->renderOn('welcome');
    }
```


If you need to pass extra data to the view specified, you just need to pass the data as arguments, just as you do normally on any other laravel controller;
```php
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = 'hello world';
        
        return (new UsersGrid())
                    ->create(['query' => User::query(), 'request' => $request])
                    ->renderOn('welcome', compact('data'));
    }
```


For eloquent relationships, its basically the same approach. Just ensure that you pass in an instance of the query builder. Like this;
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
        // load relationships
        $query = User::with(['posts', 'activities'])
        return $usersGrid
                    ->create(['query' => $query, 'request' => $request])
                    ->renderOn('welcome');
    }
}
```

Then display the grid on your view like this;
```php
{!! $grid !!}
```

You should see something like this. Feel free to check out the [sample app](http://laravel-grid.herokuapp.com). Also for reference, its source code is [here](https://github.com/leantony/laravel-grid-app)


![sample](docs/sample.png)


To enable javascript utilities such as Pjax, ensure that you have their dependencies included on your page/layout. Once you have done that, ensure that you have a `@push('grid_js')` section on your view/layout, just after the required javascript dependencies. This is the point where the grid's javscript will be injected. An example is as shown below;
```php

    <!-- dependencies e.g jquery, pjax, bootstrap js come here -->
    
    <!-- load the grid's javascipt -->
    <script src="{{ asset('vendor/leantony/grid/js/grid.js') }}"></script>
    <script>
        // setup ajax. This is required, so that the CSRF token is sent during AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <!-- the initialization javascript for any of the grid(s) you create will be injected here. Having this will ensure that the script is executed after it's dependencies have been loaded. -->
    @stack('grid_js')
```
> Just make sure that the `meta` element with the name `csrf-token` is available on your layout/blade view. E.g
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```
The grid's initialization javascript is dependent on the grid itself. A grid is initialized by an ID *(if you're curious, you can confirm this [here](https://github.com/leantony/laravel-grid/blob/13d88ca63d32a1885545fca0af014c065ea679b9/src/resources/views/grid/grid.blade.php#L189-L212)*. To read more about this, be sure to check out the [javascript](docs/javascript.md) section of the docs.

To customize the grid, check out [the customization section](docs/customization.md).

# TODO's
Following the instructions above should get you on the right track. However, if you run into any issues, please be sure to [raise it](https://github.com/leantony/laravel-grid/issues/new)

+ Add unit tests (in progress. The `passing` badge was triggered by running a dummy unit test actually. Real tests will soon be added).
+ Completion of documentation (in progress).
+ Add more customization options to published config file. Stuff like pagination, filters, buttons, etc
+ Add ability to culminate grid rows. E.g displaying a total of some sort.
+ Add ability to have a checkbox that can be used to select multiple items, after which an action can be done. E.g deleting mutiple items.
