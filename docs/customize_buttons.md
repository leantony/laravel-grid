# Customize buttons on the grid
The grid comes with 5 default buttons. This are `create`, `update`, `delete`, `view` and `export`. The property `$buttonsToGenerate` on the
generated grid allows you to quickly enable or disable these default buttons. Simply by removing them from this array
```php
    /**
     * List of buttons to be generated on the grid
     *
     * @var array
     */
    protected $buttonsToGenerate = [
        'create', 'view', 'delete', 'refresh', 'export'
    ];
```

## Adding custom ones
Within the `configureButtons` function, call the `makeCustomButton` like this;

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            // an icon for the button, as chosen from font-awesome. Defaults to null
            'icon' => 'fa-user',
            // the name of the button. Defaults to Unknown
            'name' => 'custom',
            // css class for the button. Defaults to `btn btn-default`
            'class' => 'btn btn-default',
            // function that will be called to determine if the button will be displayed. Defaults to null
            'renderIf' => function() {return true;}, 
            // a link for this button. using the function specified will get an already existing route. Otherwise you can use any of
            // laravel's helper functions to get a url. Defaults to #
            // it accepts both a string and a callbac. See the scenarios below
            'url' => route('users.index'),
            // where to the left or right with respect to other buttons would it be displayed. Higher means it will slide over to the far left, 
            // and lower means it will slide over to the far right. Its actually a sort run over the collection of buttons, and this argument
            // passed in the callback as an arg. Defaults to null
            'position' => 99,
            // if an action on it would trigger a PJAX action. Defaults to false
            'pjaxEnabled' => true, 
        ], 'toolbar'); // means this button will be placed on the toolbar. Try 'row' to place it on the rows. Defaults to 'toolbar'
    }
```

Just like that, and your custom button will be rendered on the grid


What of adding a `row` button that has a link whose url value is determined by the element being rendered on the grid?. Here's how you will do it

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-user',
            'name' => 'custom',
            'class' => 'btn btn-default',
            // $gridName represents a short singular name for the grid. E.g `Users` for a grid name resolves to `user`
            // $gridItem represents the current data item being iterated on. It should be an eloquent model instance
            'link' => function($gridName, $gridItem) {
                return route('users.show', [$gridName => $gridItem->id]);
            },
        ], 'row');
    }
```
> since the data item is only available when the loop to render the rows has began, the location of this button should be `row`. Otherwise it won't work


The same can also be applied to a `toolbar` button, but in a slightly modified way. In this case, the `link` shall be a `callable` which accepts no arguments. Like so;

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-user',
            'name' => 'custom',
            'class' => 'btn btn-default',
            // no arguments for this callback
            'link' => function() {
                return route('users.index');
            },
        ]);
    }
```

## Modifying existing buttons
Need to make a button appear to the far right/left of another?. This is how you will do it
```php
    //
    public function configureButtons()
    {
        $this->editRowButton('delete', [
            // use any number. Higher pushes it to the far right, and lower pushes it to the far left
            'position' => 3
        ]);
    }
```
> Its a simple sort on the collection of the buttons based on the `$position`. [see here](https://github.com/leantony/laravel-grid/blob/63d4160701aec0268277a5d9e698408c2e9b9375/src/Grid.php#L522-L536)


How about editing an existing button?. E.g to ensure that a user only deletes a record that they own?...This is the way you will do it

```php
    //
    public function configureButtons()
    {
        // get the user
        $user = auth()->user();
        $this->editRowButton('delete', [
            'renderIf' => function ($gridName, $item) use ($user) {
                return $user->id === $item->user_id;
            }
        ]);
    }
```
> Note that the `renderIf` callable runs on each iteration of the grid. So on each iteration you will have access to the item being iterated upon.



For a `toolbar` button, its generally the same approach, but the `renderIf` callback takes no arguments. E.g to render the `create` button only when a user is logged in
> Just note that the call is to the `editToolbarButton` in this case
```php
    //
    public function configureButtons()
    {
        $this->editToolbarButton('delete', [
            'renderIf' => function () use ($user) {
                return auth()->check();
            }
        ]);
    }
```