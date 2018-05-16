The grid comes with support for bootstrap's modal component.

You'll have to create the modal form yourself, so including the required assets alone is not enough. But don't worry. It's just like creating any other laravel view.
Assuming you have all the requirements ready, this are the steps you will follow to render a modal on your page, when you click the 'create' button.
You also need to have published the grid's assets. If you haven't done that already, run the command;

```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\GridServiceProvider"
```
> For this steps, we will be assuming you want a modal displayed when you click on the 'create' button, so that you get a form that will help you create a user. This of course also assumes you already have a grid generated already.

This are the steps to adding modal functionality;

# Include the grid's JavaScript asset on your page
Add this to your layout. Just some basic setup.
```php
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    // .. other stuff
</head>
// .. layout code

// .. other javascript assets, e.g Jquery, bootstrap, Pjax, etc
<script src="{{ asset('vendor/leantony/grid/js/grid.js') }}"></script>
<script>
    // ensure the CSRF header is sent in an AJAX request.
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@stack('grid_js')
```
> The `@stack('grid_js')` will ensure that the javascript for any grid you create is automatically injected on the page.

# Add the modal container to your page
This is a simple laravel view that comes with the grid installation. It will serve as an injection point for the modal HTML. Ideally, this view should be included on your layout.
```html
// .. layout code
<div class="container">
    @yield('content')
</div>
@include('leantony::modal.container')
// .. layout code
```

# Create the modal view
You can use the template below to get started. The `Modal` facade is available to help with rendering repeated boilerplate. All you have to do now is customize the form inputs to match your needs.
```html
{!! Modal::start($modal) !!}
<div class="form-group row">
    <label for="input_name" class="col-sm-2 col-form-label">Name:</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="input_name" name="name"
               placeholder="Enter name" value="{{ isset($user) ? $user->name : old('name')}}">
    </div>
</div>
<div class="form-group row">
    <label for="input_email" class="col-sm-2 col-form-label">Email:</label>
    <div class="col-sm-10">
        <input type="email" class="form-control" id="input_email"
               name="email" placeholder="Enter email" value="{{ isset($user) ? $user->email : old('email')}}">
    </div>
</div>
{!! Modal::end() !!}
```

Then name it sth like `users_modal.blade.php`.

# Add a controller method to handle fetching of the modal form
This is pretty straightforward. You're only writing code to load the form you've created just above and passing in the variables from the controller.
```php
/**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        $modal = [
            'model' => class_basename(User::class),
            'route' => route('users.store'),
            'action' => 'create',
            'pjaxContainer' => $request->get('ref'),
        ];

        // modal
        return view('users_modal', compact('modal'))->render();
    }
```
> We use `render()`, so that just the HTML for the form is sent as a string, instead of the layout n etc.

## Available parameters
For the input array, the following key values are required to be passed to the `Modal::start($data)` section;

| key value        | Description           | Required  |
| ------------- |:-------------:| -----:|
| `action`      | Just any sensible action name to give to the modal. E.g `create` | `true` |
| `model`      | An eloquent model that relates to this modal. Action and model are used to create a title for the modal      |   `true` |
| `route` | Url for form handling      |    `true` |
| `pjaxContainer` | Pjax container to be refreshed after submit      |    `false` |
| `method` | Form method. E.g `PUT`     |    `true` |

# Add a button to handle rendering of the modal
By default, the `create` and `view` buttons allow modal popups. For any other button, you just have to customize it as follows;
```php
/**
* Configure rendered buttons, or add your own
*
* @return void
*/
public function configureButtons()
{
    // editing the view button
    $this->editToolbarButton('create', [
       'showModal' => true,
       'dataAttributes => [
            // to optionally change the size of the modal. see https://getbootstrap.com/docs/4.0/components/modal/#optional-sizes
           'modal-size' => 'modal-sm'
       ]
    ]);
}
```

# Add a controller method to handle posting of the user's data
This should also be pretty straightforward. The major change should be returning `json` instead of redirecting or rendering a view. Any validation errors will be displayed on the modal itself.
```php
/**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        User::creating(function ($user) {
            $user->password = bcrypt($user->password);
        });
        $user = User::query()->create($request->all());
        return new JsonResponse([
            'success' => true,
            'message' => 'User with id ' . $user->id . ' has been created.'
        ]);
    }
```
This should be enough. You can then reload your page, and click on the `create` button on your grid. The modal will be loaded dynamically via AJAX, and it should pop up.
You can refer to the example application [here](http://laravel-grid.herokuapp.com), for a demo.

# Previous
[Customization](customization.md)