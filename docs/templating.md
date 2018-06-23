The grid supports custom layouts/templates, since it uses a layout to control how the grid's 
container looks like.

# Explanation
The default layout is a bootstrap 4 card element. Looks like this;
```php
<div class="row laravel-grid" id="{{ $grid->getId() }}">
    <div class="col-md-12 col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="pull-left">
                    <h4 class="grid-title">{{ $grid->renderTitle() }}</h4>
                </div>
                {!! $grid->renderPaginationInfoAtHeader() !!}
            </div>
            <div class="card-body">
                @yield('data')
            </div>
            <div class="card-footer">
                {!! $grid->renderPaginationInfoAtFooter() !!}
                {!! $grid->renderPaginationLinksSection() !!}
            </div>
        </div>
    </div>
</div>
```
The `@yield('data')` section will ensure that the grid's content is put in the view that extends this layout

# Important variables
+ `laravel-grid` => Required to allow the grid's default CSS apply styling to the buttons, etc.
+ `$grid->getId()` => Required to set a PJAX container for the grid. Otherwise, PJAX won't work
+ `$grid->renderTitle()` => If you need the grid's title displayed
+ `$grid->renderPaginationInfoAtHeader()` => Required to display sth like `Displaying 1 to 10 of x records` on your grid
+ `$grid->renderPaginationInfoAtFooter()` => Required to display same text as the header pagination, but pushed towards the left so that pagination links can be accommodated
+ `$grid->renderPaginationLinksSection()` => Required to display pagination links

# How to use
The grid's configuration allows you to change the grid's template to your own custom built one. However, this will apply to all grid's you create.
```php
/**
     * Grid templates
     */
    'templates' => [
        /**
         * The view to use for the templates
         */
        'view' => 'leantony::grid.templates.bs4-card'
    ]
```
If you need per grid customization of the above, you can call the `withCustomTemplate` function when creating your grid. Like this;
```php
$someGrid->create(['query' => User::query(), 'request' => $request])->withCustomTemplate('view_name')
```

# Previous
[Customization](customization.md)