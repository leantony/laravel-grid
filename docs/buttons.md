The buttons are used to offer interactivity on the grid. Much like any other web app. 
The grid comes with 5 default buttons. This are `create`, `update`, `delete`, `view` and `export`. 
The property `$buttonsToGenerate` on the generated grid allows you to quickly enable or disable these default buttons. Simply by removing them from the array.

# Default buttons
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
# Button attributes
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
// can be one of `row` or `toolbar`. Defaults to toolbar
'type' => 'toolbar',

// HTML5 data attributes that you may use in javascript. You need to supply them as a key value pair, with the key only having the data-{name} part. 
// E.g 'data-url' will be represented as ['url' => 'someurl']
// defaults to an empty array
'dataAttributes' => [],

// the HTML ID of the grid. Used for PJAX actions to be added as the target container. 
// No need to change this. It is added for you.
'gridId' => 'grid-id',

// css class for the button. Defaults to `btn btn-default`
'class' => 'btn btn-default';

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

# Button locations
The buttons can be located on two areas on the grid. The `toolbar` which is on the top and the `row` which is on each data element on the grid.
+ Row - useful for user actions that are data dependent. E.g viewing a record, or deleting it.
+ Toolbar - useful for user actions that are not data dependent. E.g creating a record, exporting the data, refreshing the grid, printing, etc.

# Button key
The buttons are added to an array of buttons once created/customized. A key uniquely identifies a button and it is simply the slug (`str_slug`) of the button's name. So if your button is called
`Create user`, then you'll use `create-user` to reference it.

# Editing buttons
For this action, you need to call the `editRowButton` or `editToolbarButton` depending on where your button is. Of course you also need to supply the `key` or `name` of the button you are editing. If the button does not exist, an error will be triggered.

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
