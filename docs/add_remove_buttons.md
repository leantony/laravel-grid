# Customize buttons on the grid

## Adding custom ones
Within the `configureButtons` function, call the `makeCustomButton` like this;

```php
public function configureButtons()
    {
        $this->makeCustomButton([
            // an icon for the button, as chosen from font-awesome. Defaults to null
            'icon' => 'fa-user',
            // the name of the button. Defaults to unknown
            'name' => 'custom',
            // css class. Defaults to `btn btn-default`
            'class' => 'btn btn-default',
            // function that will be called to determine if the button will be displayed. Defaults to null
            'beforeRender' => function() {return true;}, 
            // a link for this button. using the function specified will get an already existing route. Otherwise you can use any of
            // laravel's helper functions to get a url. Defaults to #
            'link' => $this->getRouteLinkFor('index'),
            // where to the left or right with respect to other buttons would it be displayed. Higher means it will slide over to the far left, 
            // and lower means it will slide over to the far right. Its actually a sort run over the collection of buttons, and this argument
            // passed in the callback as an arg. Defaults to null
            'position' => 99,
            // if an action on it would trigger a PJAX action. Defaults to false
            'pjaxEnabled' => true, 
        ], 'toolbar'); // means this button will be placed on the toolbar. Try 'row' to place it on the rows. Defaults to 'row'
    }
```

Just like that, and your button will be rendered on the grid

What of a row button that has a link whose url value is determined by the element being rendered on the grid?. Here's how you will do it
```php

```

How about editing an existing button?. E.g making the delete button only be accessible to logged in users?. Just call the `editRowButton` and pass in the name
and whatever arguments you need to modify. If the attribute does not exist, it will be added to the button
> The function `editRowButton` is used, since the `delete` button is situated on the `row` of every grid record. To edit a `toolbar` button, call the 
`editToolbarButton` the same way

```php
    //
    public function configureButtons()
    {
        // edit an existing button and add the callback
        $this->editRowButton('delete', [
            'beforeRender' => function() {
                return auth()->check();
            }
        ]);
    }
```
The `beforeRender` function runs on each iteration of the grid. This may 