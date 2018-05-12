# Laravel grid

[![Build Status](https://travis-ci.org/leantony/laravel-grid.svg?branch=master)](https://travis-ci.org/leantony/laravel-grid)

This package allows rendering of data via a tabular format (grid). The grid uses bootstrap classes to style the `table` elements. Icons used are from `font-awesome`, and most of the functionality is insipred by the yii2's framework's gridview widget. 

## Requirements
The grid supports [pjax](https://github.com/defunkt/jquery-pjax) out of the box. For pjax support on laravel, try - [spatie/laravel-pjax](https://github.com/spatie/laravel-pjax), or create the middleware on your own using [jeffrey way's middleware.](https://gist.github.com/JeffreyWay/8526696b6f29201c4e33)

+ Laravel 5.5 and above
+ Bootstrap 3|4 (for the styling) - see installation section below
+ Font awesome (for icons)
+ Jquery (required by bootstrap and the package's javascript)
+ Jquery pjax (quickly view data without having to reload the page)
+ [Block UI](https://github.com/malsup/blockui) (optional - to 'block' the UI when doing an AJAX request that might presumably take a while).
+ [Date picker](https://github.com/dangrossman/bootstrap-daterangepicker.git) (optional - for the single date & date range filters)


## Installation
The package is available on packagist. If you need bootstrap 3 support, please run;
```php
composer install leantony/laravel-grid ~1.0
```
For `bootstrap 4`, please run
```php
composer install leantony/laravel-grid "2.0.x-dev"
```

You can then publish the resources (views) and assets (css & js). CSS and JS are needed on your layout so that the grid works appropriately.

```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider"
```

> You can publish the assets and views separately by passing the `--tag` argument to the command. 
For the argument values, try `assets`, `views`, `config` for js|css assets, views and config respectively.


When the package is updated, it is highly likely that you will also need to update the javascript assets. To do that, run this command below after an update;
```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider" --tag=assets --force
```
> You can also place this command in composer so that it is executed automatically on each update run. Like this;
```php
// ... composer config
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover",
    "@php artisan vendor:publish --provider=\"Leantony\\Grid\\Providers\\ServiceProvider\" --tag=assets --force"
]
```


## Usage

### creating the grid
A laravel command is available to make it easy to create grids. 
Once installed, just run the command:
```php
php artisan make:grid --model="{modelClass}"
```
Just make sure you replace `{modelClass}` with your actual `eloquent` model class. E.g
```php
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
Just make sure that you do not call `->get()` on the query.

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
        return (new UsersGrid(['user' => $user])) // you can then use it as $this->user within the class. It's set implicitly using the __set() call
                    ->create(['query' => User::query(), 'request' => $request])
                    ->renderOn('welcome');
    }
```
> Adding `$user` above as part of the key value pair array on the create method would achieve the same results as above.


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

For eloquent relationships, its basically the same approach. Like this;
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
And once again, just make sure that you do not call `->get()` on the query.

### Adding assets
Be sure to also include the necessary javascript and css assets on your layout. An example layout is as shown below;
```html
<!-- sample laravel layout -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My application</title>
    <!-- font awesome (required) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />
    <!-- progress bar (not required, but cool) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.css" />
    <!-- bootstrap (required) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" />
    <!-- date picker (required if you need date picker & date range filters) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <!-- grid's css (required) -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/leantony/grid/css/grid.css') }}" />
</head>
<body>

<nav class="navbar navbar-expand-sm bg-primary navbar-dark">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="navbar-brand" href="/">My appliation</a>
        </li>
    </ul>
</nav>

<div class="container" style="margin-bottom: 100px;">
    <div class="row">
        @yield('content')
    </div>
</div>

<!-- modal container (required if you need to render dynamic bootstrap modals) -->
@include('leantony::modal.container')

<!-- progress bar js (not required, but cool) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
<!-- moment js (required by datepicker library) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<!-- jquery (required) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<!-- popper js (required by bootstrap) -->
<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
<!-- bootstrap js (required) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<!-- pjax js (required) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js"></script>
<!-- block ui (block ui when doing an ajax request - not required, but cool) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<!-- datepicker js (required for datepickers) -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- required to supply js functionality for the grid -->
<script src="{{ asset('vendor/leantony/grid/js/grid.js') }}"></script>
<script>
    // send csrf token (see https://laravel.com/docs/5.6/csrf#csrf-x-csrf-token) - this is required
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // for the progress bar (required for progress bar functionality)
    $(document).on('pjax:start', function () {
        NProgress.start();
    });
    $(document).on('pjax:end', function () {
        NProgress.done();
    });
</script>
<!-- entry point for all scripts injected by the generated grids (required) -->
@stack('grid_js')
</body>
</html>
```

### Rendering the grid
To display your grid, simply add this to your view. Or any appropiate variable you passed into the `renderOn` method.
```php
{!! $grid !!}
```
For a quick demonstration, be sure to check out the demo [here](http://laravel-grid.herokuapp.com/). The demo's source code is also [available on github](https://github.com/leantony/laravel-grid-app).

## Multiple grids on the same page
Just pass a name argument to the `renderOn` method. Like this
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
                    ->renderOn('welcome', [], 'users'); // first argument is the viewname, then data, then the name of the variable to be sent to the view representing the grid
    }
}
```
Then render as;
```php
{!! $users !!}
```

# Further customizations
To customize the grid, check out [the customization section](https://github.com/leantony/laravel-grid/wiki/Customization). Following the instructions above should get you on the right track. However, if you run into any issues, please be sure to [raise it](https://github.com/leantony/laravel-grid/issues/new).

# More docs
For more docs, please checkout the [wiki](https://github.com/leantony/laravel-grid/wiki).

> The docs linked in the wiki may only apply to version 1.0, so please bear with me as I update them progressively.
