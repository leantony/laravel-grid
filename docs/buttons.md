The grid comes with 5 default buttons. This are `create`, `update`, `delete`, `view` and `export`. The property `$buttonsToGenerate` on the generated grid allows you to quickly enable or disable these default buttons. Simply by removing them from the array.
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

## Button attributes
The button's PHP class uses the magic functions `__set` and `__get`, so you can actually add any property, and it will be magically set as a `public` property. The already existing attributes are shown below, and their sample values / defaults.
```php
// an icon for the button, as chosen from font-awesome. Defaults to null
'icon' => 'fa-user';

// the name of the button. Defaults to 'unknown'.
// the name is actually set on the view itself using the ucwords function, so you may pass your name in lowercase.
'name' => 'custom';

// the title of the button. Will be added as a `title` HTML attribute. Defaults to an empty string
'title' => '',

// the type of the button. Defines the location where the button will be placed on the grid.
// can be one of `rows` or `toolbar`. Defaults to toolbar
// Preset constants `TYPE_ROW` and `TYPE_TOOLBAR` can also be used
'type' => 'toolbar',

// HTML5 data attributes that you may use in javascript. You need to supply them as a key value pair, with the key only having the data-{name} part. 
// E.g 'data-url' will be represented as ['url' => 'someurl']
// defaults to an empty array
'dataAttributes' => [],

// the HTML ID of the grid. Used for PJAX actions to be added as the target container. 
// No need to change this. It is added for you.
'gridId' => 'grid-id',

// css class for the button. Defaults to `btn btn-info`
'class' => 'btn btn-info';

// function that will be called to determine if the button will be displayed. Defaults to null
'renderIf' => function() {return true;};

// a link for this button. You can use any of laravel's helper functions to get a url. 
// Defaults to '#'. It accepts both a string and a callback. See the scenarios below
'url' => route('users.index');

// where to the left or right with respect to other buttons would it be displayed. 
// Higher means it will slide over to the far left and lower means it will slide over to the far right. 
// It's actually a sort callback run over the collection of buttons, and this argument passed in the callback as an argument. 
// Defaults to `null`
'position' => 99;

// if a user click on it would trigger a PJAX action. Defaults to false
'pjaxEnabled' => true;

// if a user clicks on it, a modal form will be triggered. Defaults to false
'showModal' => true
```

## Button locations
The buttons can be located on two areas on the grid. The `toolbar` which is on the top and the `row` which is on each data element on the grid.
+ Row - useful for user actions that are data dependent. E.g viewing a record, or deleting it.
+ Toolbar - useful for user actions that are not data dependent. E.g creating a record, exporting the data, refreshing the grid, printing, etc.

## Editing buttons
For this action, you need to call the `editRowButton` or `editToolbarButton` depending on where your button is. Of course you also need to supply the `name` of the button you are editing. If the button does not exist, an error will be triggered.
```php
public function configureButtons()
    {
        // editing the view button
        $this->editRowButton('view', [
            'name' => 'view item',
        ]);
    }
```
```php
public function configureButtons()
    {
        // editing the refresh button
        $this->editToolbarButton('refresh', [
            'name' => 'Refresh grid',
        ]);
    }
```

## Adding custom buttons
Within the `configureButtons` function, call the `makeCustomButton` and pass in any of the attributes listed above in the array.

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'name' => 'Home',
            'url' => url('/'),
        ], static::$TYPE_TOOLBAR); // means this button will be placed on the toolbar. Try 'row' to place it on the rows. Defaults to 'toolbar'
    }
```
You can also choose to call `addRowButton` or `addToolbarButton` to achieve the same task. These methods accept an instance of the `GenericButtonClass`, which you can create and set the attributes by chaining the methods (see below).
```php
public function configureButtons()
    {
        // the first argument is specifies the key by which the button will be referenced by in the buttons array. 
        // This key can later be used when editing the button
        $this->addToolbarButton('home', (new GenericButton())->setName('Home')
                                                             ->setUrl('/'));
    }
```
Just like that, and your custom button will be rendered on the grid when you reload your page.

### Adding buttons with url's dependent on the data items
It's inevitable that a scenario like this would pop up. In this case, the `url` property can be defined as a callback, like this;
```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-user',
            'name' => 'custom',
            'class' => 'btn btn-info',
            // $gridName represents a short singular name for the grid. E.g `Users` for a grid name resolves to `user`
            // $gridItem represents the current data item being iterated on. It should be an eloquent model instance
            'url' => function($gridName, $gridItem) {
                return route('users.show', [$gridName => $gridItem->id]);
            },
        ], static::$TYPE_ROW);
    }
```
> since the data item is only available when the loop to render the rows has began, the location of this button should be `row`. Otherwise it won't work.


It's worth noting that the callback is not restricted to buttons rendered on the `rows` section. The same can also be applied to a `toolbar` button, but in a slightly modified way. In this case, the `url` shall be a `callable` which accepts no arguments.

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-user',
            'name' => 'custom',
            'class' => 'btn btn-info',
            // no arguments for this callback
            'url' => function() {
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
> It's actually a simple sort on the collection of the buttons based on the `$position` attribute. [see here](https://github.com/leantony/laravel-grid/blob/63d4160701aec0268277a5d9e698408c2e9b9375/src/Grid.php#L522-L536)


### Enforcing access to a button based on the data item
Sometimes you might need to display a specific button, only when say, the user owns a record. Consider a case where you want a user to only `delete` records that they have created/own, and only view the others. Here's how you will do it;
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
> The `renderIf` callable runs on each time a row is rendered. To prevent re-execution of a function call to get data to be used in the callback, be sure to pass the data to the callback using the `use($var)` keyword.


And just like the section on adding buttons, it's generally the same approach for a button on the `toolbar`. The `renderIf` callback takes no arguments. Consider a case where you need to render the `create` button only when a user is logged in. Here's how you will do it;

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
> For the toolbar buttons, the callback will be called only once, so you can call other functions within it, without incurring multiple calls it it.

## Customized rendering
If you need your button for example to be rendered using a custom view that you created, here's how you will do it.
```php
public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-print',
            'name' => 'print',
            'class' => 'btn btn-info',
            'renderCustom' => function ($data) {
                // just make sure the custom view exists
                return view('resources.buttons.print', $data)->render();
            },
            'url' => url("/print")
        ]);
    }
```

## Enable/disable modal popup
Please read the section on [how to add a modal popup](modals.md) so that you add a popup to your buttons action. Nonetheless, this is how you will enable/disable one.
```php
public function configureButtons()
    {
       // enabling
        $this->editToolbarButton('create', [
            // set to false to disable
            'showModal' => true
        ]);
    }
```

## Enable/disable pjax on button click
The grid supports PJAX out of the box. You just need to have the JQuery, the [pjax library](https://github.com/defunkt/jquery-pjax) and a [middleware handler](https://gist.github.com/JeffreyWay/8526696b6f29201c4e33) on your project. The pjax container is automatically set to the grid ID itself, and passed into javascript. Any generated PJAX events would target this container. Here's how to enable/disable PJAX on your buttons.
```php
public function configureButtons()
    {
       // enabling
        $this->editToolbarButton('create', [
            // set to false to disable
            'pjaxEnabled' => true,
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
        $this->clearButtons(static::$TYPE_ROW);
    }
```
> Both the above functions will lead to the buttons not being displayed regardless of the `$displayButtons` option.

# Previous
[Customization](customization.md)
