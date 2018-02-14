# Examples
See the code samples below to get started on how to use the grid to maximal effect


## Add a custom button
```php
    // 
    public function configureButtons()
    {
        $this->makeCustomButton([
            'icon' => 'fa-user',
            'name' => 'custom',
            'class' => 'btn btn-default',
            'beforeRender' => function() {return true;},
            'link' => $this->getRouteLinkFor('index'),
            'position' => 99,
            'pjaxEnabled' => true,
        ], 'toolbar');
    }
```

## Edit an existing button
```php
    //
    public function configureButtons()
    {
        // e.g to ensure that the user has logged in before deleting the record
        // the button will not be rendered if the callback returns false
        $this->editRowButton('delete', [
            'beforeRender' => function() {
                return auth()->check();
            }
        ]);
    }
```