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

Just like that, and your custom button will be rendered on the grid.


### Adding buttons with url's dependent on the data items
It's inevitable that a scenario like this would pop up. In this case, the `url` property can be defined as a callback, like so;

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


It's worth noting that the callback is not restricted to buttons rendered on the `rows` section. The same can also be applied to a `toolbar` button, but in a slightly modified way. In this case, the `link` shall be a `callable` which accepts no arguments. Like so;

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
> Its a simple sort on the collection of the buttons based on the `$position` attribute. [see here](https://github.com/leantony/laravel-grid/blob/63d4160701aec0268277a5d9e698408c2e9b9375/src/Grid.php#L522-L536)


### Enforcing actions to a button based on the data item
Sometimes you might need to display a specific button, only when say, the user owns a record. Consider a case where you want a user to only `delete` records that they
have created/own, and only view the others. As with the [adding part](#Adding buttons with url's dependent on the data items), where a callback was used, the
same applies here. Like this;

```php
    //
    public function configureButtons()
    {
        // get the user
        $user = auth()->user();
        $this->editRowButton('delete', [
            // only render this button if the user logged in owns the record
            'renderIf' => function ($gridName, $item) use ($user) {
                // assuming there exists a `user_id` field on your data
                return $user->id === $item->user_id;
            }
        ]);
    }
```
> The `renderIf` callable runs on each iteration of the grid. So on each iteration you will have access to the item being iterated upon.



And just like the section on adding buttons, its generally the same approach for a button on the `toolbar`. The `renderIf` callback takes no arguments. 
Consider a case where you need to render the `create` button only when a user is logged in. Here's how you will do it;

> Just note that the call is to the `editToolbarButton` in this case
```php
    //
    public function configureButtons()
    {
        $this->editToolbarButton('create', [
            'renderIf' => function () use ($user) {
                return auth()->check();
            }
        ]);
    }
```

## Removing all buttons on the grid
This is possible. Just call the `clearAllButtons` function. Like this;
```php
    //
    public function configureButtons()
    {
        $this->clearAllButtons();
    }
```

## Removing buttons per section
E.g if you want to remove the buttons on the `rows`
```php
    //
    public function configureButtons()
    {
        $this->clearButtons('rows');
    }
```
> Both the above functions will lead to the buttons not being displayed regardless of the `$displayButtons` option.