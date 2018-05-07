This laravel package allows rendering of data via a tabular format (grid). The grid uses bootstrap classes to style the `table` elements. Icons used are from `font-awesome`, and most of the functionality is inspired by the yii2's framework's gridview widget.

## Installation
The package is available on packagist. The latest version is `2.0.x-dev`. If you need bootstrap 3 support, please run;
```php
composer install leantony/laravel-grid ~1.0
```
For `bootstrap 4`, please install the latest version, by running;
```php
composer install leantony/laravel-grid "2.0.x-dev"
```

You can then publish the resources (views) and assets (css & js). CSS and JS are needed on your layout so that the grid works appropriately.

```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider"
```

> You can publish the assets and views separately by passing the `--tag` argument to the command. 
For the argument values, try `assets`, `views`, `config` for js|css assets, views and config respectively.


When the package is updated, it is highly likely that you will also need to update the javascript assets. To do that, run this command below after an update;
```php
php artisan vendor:publish --provider="Leantony\Grid\Providers\ServiceProvider" --tag=assets --force
```
> You can also place this command in composer so that it is executed automatically on each update run. Like this;
```php
// ... composer config
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover",
    "@php artisan vendor:publish --provider=\"Leantony\\Grid\\Providers\\ServiceProvider\" --tag=assets --force"
]
```

# Next up
[How to use](usage.md)