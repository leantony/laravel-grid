## Creating grids
> Make sure that you have configured your routes with route names. The grid uses this internally to generate urls.

Use the provided generator command like so;
```bash
php artisan make:grid --model="{modelclass}"
```

For example, to make a grid that would display all users
```bash
php artisan make:grid --for="App\\User::class"
```
This would create a minimalistic grid, with the ability to do crud operations to the item e.g `user`. 
Its up to you though to design the forms and views.

## Rendering the grid
The grid constructor takes an array of key value paired arguments. You can pass any element to the array, as a key value pair as the `__set` method shall take care of it.
Now, for filtering the data, and performing pagination, the grid requires an instance of `Illuminate\Database\Eloquent\Builder` and the request instance from `Illuminate\Http\Request`.

### basic rendering
```php
public function index(Request $request){
    // build a query. You can even include relationships if you need to
    $query = User::query();
    
    $grid = new UsersGrid(['query' => $query, 'request' => $request]);
    
    return view('users.index', compact('grid'));
}
```
Then on your view;
```php
{!! $grid !!}
```
> Once the grid has been sent to the view, all that's required on the view is `{{ $grid }}`
The implicit call to the `__toString` method would take care of the rest.
