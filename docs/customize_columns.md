# Customize the grid columns
This section describes the properties that apply to the `columns` property that is needed to render the grid.


## Column name
This is passed as a `key` in the array. Each array key should ideally **represent a column that exists** on the table that represents your eloquent model. 
> If you specify a column that does not exist on your model, you need to customize how the data would be fetched


## Column data
This is the `value` of the array. It has a variety of key value pairs too, which will be explained below;


### sort
+ Possible values = `boolean`
+ Required = `false`

This specifies if a column is sortable. Defaults to `true`. View sample usage below;
```php
"name" => ["sort" => true];
"name" => ["sort" => false]
```


### label
+ Possible values = `string`
+ Required = `false`

This represents a readable name for the `column name`. Defaults to `null` and the regular expression - `/[^a-z0-9 -]+/` is used where each valid match is replaced with a space. View example usage below;
```php
"name" => ["label" => "Username"];
"name" => ["label" => "First Name"];
// set to null or ommit the key to use the available defaults
"name" => ["label" => null];
```


### filter
+ Possible values = `array`
+ Required = `false`

This represents a `filter` which will be rendered in a new `<tr>` below the column name. The possible values can be;
+ `boolean` **enabled** - Specifies if the filter is enabled for this column. If not supplied, `false` is assumed.
+ `string` **operator** - Specifies the sql operator that will be applied to the value entered by the user. Possible values are `=`, `like`, etc. Default is `=`. This value is not case sensitive.
+ `string` **type** - specifies the type of filter. Possible values are `text`, `date`, `select` and `daterange`. These are documented on the [filters section](customize_filters.md). Defaults to `text`.
+ `callable` **query** - Specifies a custom query that will be called to filter the data for this column based on its value. Defaults to `null`. The function expects these 3 arguments - `query` which is an instance of the eloquent query builder, `columnName` which is the name of the column you need to filter, and `userInput` is the user's input.

```php
function($query, $columnName, $userInput) {
    //
}
```
View sample usage below;
```php
// enabling the filter on a column
"name" => [
    "filter" => [
        "enabled" => true,
        "operator" => "="
    ],
]
// adding the filter type
"name" => [
    "filter" => [
        "enabled" => true,
        "operator" => "=",
        "type" => "text"
    ],
]
// adding a dropdown filter
"name" => [
    "filter" => [
        "enabled" => true,
        "operator" => "=",
        "type" => "select",
        "data" => Users::pluck('name', 'id'),
    ],
]

// custom query
"name" => [
    "filter" => [
        "enabled" => true,
        "query" => function($query, $columnName, $userInput) {
            return $query->where("name", "like", "%" . $userInput . "%")
                ->orWhere("first_name", "like", "%" . $userInput . "%")
        }
    ],
]
```
> Note that all columns with filters will be joined during querying using the `and` operator. This can be configured in `app/config/grids.php` (configuration coming soon)

> The `operator` option is ignored when you specify a query

### styles
+ Possible values = `array`
+ Required = `false`

This represents css styles for the grid columns, as well as the rows. possible values can be;
+ `string|callable` **column** - Defines the styles for the `column` of the grid. E.g if you want one of the columns to be have a larger width. Defaults to `col-md-2`. For a callable, this you may define it;
```php
function() {
    //
}
```
+ `string|callable` **row** - Defines the styles for the `row` of the grid. E.g if you want one of the rows to be colored differently based on the data. Defaults to `null`. The function expects these 2 arguments - `gridName` which is the name of the grid, and `gridItem` is the current grid data item.
```php
function($gridName, $gridItem) {
    //
}
```
Check sample usage below;
```php
// default
"name" => [
    "styles" => [
        "column" => "col-md-2",
        "row" => null
    ],
]
// adding row style as a string
"name" => [
    "styles" => [
        "column" => "col-md-2",
        "row" => "success"
    ],
]
// adding row style using a function. E.g to highlight the currently logged in user on the grid
// assuming the grid displays a list of users
$loggedInUser = auth()->user();
"name" => [
    "styles" => [
        "column" => "col-md-2",
        "row" => function($gridName, $gridItem) use ($loggedInUser) {
            return $gridItem->id === $loggedInUser->id ? "success" : null;
        }
    ],
]
```


### search
+ Possible values = `array`
+ Required = `false`

This represents a global `search_field` which will be rendered on the grid toolbar. This has the ability to search the whole table, based on fields you define. The possible values can be;
+ `boolean` **enabled** - Specifies if the filter is enabled for this column. If not supplied, `false` is assumed.
+ `string` **operator** - Specifies the sql operator that will be applied to the value entered by the user. Possible values are `=`, `like`, etc. Default is `like`.
> The default operator is `like`. This operator value is not case sensitive.
+ `callable` **query** - Specifies a custom query that will be called to search the data for this column based on its value. Defaults to null. The function expects these 3 arguments - `query` which is an instance of the eloquent query builder, `columnName` which is the name of the column you need to filter, and `userInput` is the user's input.

```php
function($query, $columnName, $userInput) {
    //
}
```

+ `boolean` **useFilterQuery** - Specifies if the `query` supplied on the `filter` option will be used for searching. Defaults to `false`

Check sample usage below;
```php
// basic
"name" => [
    "search" => [
        "enabled" => true,
        "operator" => "like",
    ],
]

// using a custom query
"name" => [
    "search" => [
        "enabled" => true,
        "query" => function($query, $columnName, $userInput) {
            return $query->where("name", "like", "%" . $userInput . "%")
                ->orWhere("first_name", "like", "%" . $userInput . "%")
        }
    ]
]

// using the filter query to perform search. Of course you need to have set up the `query` option for the filter
"name" => [
    "search" => [
        "enabled" => true,
        "useFilterQuery" => true
    ],
    "filter" => [
        "enabled" => true,
        "query" => function($query, $columnName, $userInput) {
            return $query->where("name", "like", "%" . $userInput . "%")
                ->orWhere("first_name", "like", "%" . $userInput . "%")
        }
    ],
]
```

> Just like the filter option, the `operator` option is ignored when you specify a query


## sort
possible values = `boolean`
Required = `false`
Defaults to = `true`

Defines if a column would be sorted, when clicked on. Check sample usage below;
```php
"name" => ["sort" => true];
```


### presenter
possible values = `string|callable`
Required = `false`
Defaults to = `null`

Allows you to specify a presenter that would be used to render the column value. If a string value is specified, it would be called on the
[laracasts presenter](https://github.com/laracasts/Presenter). E.g
```php
// laracasts presenter class
class UserPresenter extends Presenter {

    public function fullName()
    {
        return $this->first . ' ' . $this->last;
    }

    public function accountAge()
    {
        return $this->created_at->diffForHumans();
    }

}

// user model
use Laracasts\Presenter\PresentableTrait;

class User extends \Eloquent {

    use PresentableTrait;

    protected $presenter = 'UserPresenter';

}

// grid column
"name" => [
    "presenter" => "fullName",
]
```
Alternatively, you can specify the `presenter` as a callback that takes in two arguments - columnData, and columnName
```php
"name" => [
    "query" => function($columnData, $columnName) {
        // do whatever you want to display the data for the `name` column
    }
]
```

### data
possible values = `string|callable`
required = `false`
defaults to = `$data->${column_name}`. E.g `name` will be `$data->name`


### date
possible values =`boolean`
required = `false`
defaults to = `false`

Specifies that the column in use is a `date`.
```php
"created_at" => [
    "date" => true,
]
```


### dateFormat
possible values =`string`
required = `false`
defaults to = `Y-m-d`

Allows the formatting of a grid column whose `date` attribute is set to true. E.g
```php
"created_at" => [
    "date" => true,
    "dateFormat" => "l jS \of F Y h:i:s A"
]
```


### raw
possible values = `boolean`
required = `false`
defaults to = `false`.

Used to render values as is (will be rendered within {!! !!}). This property relies on the `data` property. For example, if you need to render an image,
you would obviously need to show it as HTML.

```php
"avatar" => [
    "raw" => true,
    "data" => function ($columnData, $columnName) {
        // like for instance, displaying an image on the grid...
        return new HtmlString(sprintf('<img src="%s" class="img-responsive" alt = "%s" width="40">', asset($columnData->{$columnName}), 'alternative'));
    },
]
```

### renderIf
possible values = `callable`
Required = `false`
Defaults to = `null`

Defines a function that would be called to determine if a column would be rendered. Check sample usage below;
```php
// render the column, only if the user is logged in
"name" => [
    "renderIf" => function() {
        return auth()->check();
    }
]
```
> Note that here the callable function cannot use the grid's data because when the columns are rendered, the data has not been iterated over yet.


### export
possible values = `boolean`
Required = `false`
Defaults to = `true`

Defines if a column would be exported, when an option to export is chosen. Check sample usage below;
```php
"name" => ["export" => true];
```
