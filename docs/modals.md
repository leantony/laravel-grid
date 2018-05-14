The grid comes with support for bootstrap's modal component.

You'll have to create the modal form yourself, so including the required assets alone is not enough. But don't worry. It's just like creating any other laravel view.
Assuming you have all the requirements ready, this are the steps you will follow to render a modal on your page, when you click the 'create' button.
You also need to have published the grid's assets. If you haven't done that already, run the command;

```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider"
```
> For this steps, we will be assuming you want a modal displayed when you click on the 'create' button, so that you get a form that will help you create a user. This of course also assumes you already have a grid generated already.

Also ensure that your `button` is set to work with PJAX, when clicked. You can read [this section](https://github.com/leantony/laravel-grid/wiki/Customize-buttons#enabledisable-pjax-on-button-click) to see how this can be achieved.

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
```php
// .. layout code
<div class="container">
    @yield('content')
</div>
@include('leantony::modal.container')
// .. layout code
```

# Create the modal view
This is identical to creating any laravel view. You may copy the sample included with the package, and this can be found once you publish the resources for the package. The copy can be found at `resources/views/vendor/leantony/modal/form-sample.blade.php`. Feel free to alter it to suit your needs, while avoiding the obvious parts - preset CSS class names and preset HTML id's, etc since they are used in javascript.

