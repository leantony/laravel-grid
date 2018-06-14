# Rendering the grid
Rendering the grid on a view should be as easy as calling the already supplied `renderOn` method. This method
actually just calls `view()` and does some selective rendering. And of course, you are free to add any of your data as usual.
Here's how it looks like;
```php
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
```
The grid by default is sent to the view using the method above as `$grid`. And since it contains HTML markup, it needs to be rendered
like this;
```php
{!! $grid !!}
```
From the method, passing a custom parameter value to `as` argument should change the name `$grid`

Alternatively, you can also render a grid by doing this;
> Assuming it's a roles grid
```php
$rolesQuery = Roles::query()
$roles = $rolesGrid->create(['query' => $rolesQuery, 'request' => $request]);
return view('home.index', compact('roles'));
```
The above grid would render, because the grid overrides `__toString`, so just placing the grid variable on your view should trigger that toString implementation.
Which in turn will render the grid itself.

## Rendering more than one grid
Using the example above, it would be as easy as passing in another variable into the view method, and rendering it on the view itself. 
For example, consider a case where you need to render `users` and `roles` as grids on one page. *This assumes you already generated the grids for yourself*. This is how you will go about it;
> UsersController, for example
```php
/**
     * Display a listing of the resource.
     *
     * @param UsersGridInterface $usersGrid
     * @param RolesGridInterface $rolesGrid
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(UsersGridInterface $usersGrid, RolesGridInterface $rolesGrid, Request $request)
    {
       // define query for roles
       $rolesQuery = Roles::query();
        // define query for users
        $usersQuery= User::query();
        // any data you need sent to the view....
        $data = ['somedata'];
        // define users grid
        $users = $usersGrid->create(['query' => $usersQuery, 'request' => $request]);
        // define roles grid
        $roles = $rolesGrid->create(['query' => $rolesQuery, 'request' => $request]);
        // send to a view
        return view('home.index', compact('users', 'roles', 'data'));
    }
```
> home.index view, for example
Now on your view, you'll just render them all on one page like this;
```html
<div class="col-md-12">
     {!! $users !!}
</div>
<hr>
<div class="col-md-12">
     {!! $roles!!}
</div>
```

# Next up
[Customizing the grid](customization.md)