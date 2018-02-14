# Data rendering
This package allows rendering of data via a tabular format (grid). 
The grid uses bootstrap classes to style the `table` elements. Icons used are from `font-awesome`

## How do I create a grid
A laravel command is available to make it easy to create grids. Once installed, just run the command `php artisan make:grid --model={modelClass}`.
Just make sure you replace `modelClass` with your actual `eloquent` model class. (full namespace).
Once this happens, a grid will be generated. Default namespace for grid generation is `App\Grids`

# How do I customize the buttons available
Edit the `$buttonsToGenerate` field. Note that this affects the default buttons. If you want to add a custom one for yourself, see the section below

# How to I add my own button
Just call the `$makeCustomButton(array $attrs, string $section)` function like this;
```php
    // 
    public function configureButtons()
    {
        $this->makeCustomButton([
            // an icon for the button, as chosen from font-awesome
            'icon' => 'fa-user',
            // the name of the button
            'name' => 'custom',
            // css class
            'class' => 'btn btn-default',
            // function that will be called to determine if the button will be displayed
            'beforeRender' => function() {return true;}, 
            // a link for this button
            'link' => $this->getRouteLinkFor('index'),
            // where to the left or right with respect to other buttons would it be displayed. Higher means it will slide over to the far left, and lower means it will slide over to the far right
            'position' => 99,
            // if an action on it would trigger a PJAX action
            'pjaxEnabled' => true, 
        ], 'toolbar'); // means this button will be placed on the toolbar. Try 'row' to place it on the rows
    }
```
> Just make sure you call it within the `configureButtons` function

# How do I prevent a button from being displayed
Add an attribute to the button in question, called `beforeRender`. It needs to be a callable, which returns true or false. E.g
to display a button only when a user is logged in
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

