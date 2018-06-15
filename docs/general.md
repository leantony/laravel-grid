The grid consists both rows and columns - but of course, since it's all but a HTML table. And just like the `buttons`, also the rows and columns can be customized. But first, they have to be added to the grid. If you used the command available to generate the grid, some of your model columns should already be there. Below are sample columns from a generated grid copied from the [sample app's code](https://github.com/leantony/laravel-grid-app/blob/6e96abdb7c1d5c9616d6913752a7f21ccfc65c45/app/Grids/UsersGrid.php#L44);
```php
     /**
     * Set the columns to be displayed.
     *
     * @return void
     * @throws \Exception if an error occurs during parsing of the data
     */
    public function setColumns()
    {
        $this->columns = [
            "id" => [
                "label" => "ID",
                "filter" => ["enabled" => true, "operator" => "="],
                "styles" => ["column" => "grid-w-10"]
            ],
            "name" => [
                "search" => ["enabled" => true],
                "filter" => ["enabled" => true, "operator" => "="]
            ],
            "role_id" => [
                'label' => 'Role',
                'export' => false,
                'search' => ['enabled' => false],
                'presenter' => function ($columnData, $columnName) {
                    return $columnData->role->name;
                },
                'filter' => [
                    'enabled' => true,
                    'type' => 'select',
                    'data' => Role::query()->pluck('name', 'id')
                ]
            ],
            "email" => [
                "search" => ["enabled" => true],
                "filter" => ["enabled" => true, "operator" => "="]
            ],
            "created_at" => [
                "sort" => false, "date" => true,
                "filter" => ["enabled" => true, "type" => "date", "operator" => "<="]
            ]
        ];
    }
```
The array is an associative array, and the keys and values define how the grid columns are rendered.

# Keys
The `keys` represent the model's attributes (actual DB columns). You are actually free to add your own custom attributes, but just ensure that you also define a custom method to render the data. See the section below on [the `data` attribute](https://github.com/leantony/laravel-grid/wiki/Customize-columns#data).

## Values
The `values` represent customizable attributes that control how the data item for that model attribute will be rendered. This will be discussed below.

### sort
+ Possible values = `boolean`
+ Required = `false`

This specifies if a column is sortable. Defaults to `true`. View sample usage below;
```php
"name" => ["sort" => true];
"name" => ["sort" => false]
```

### label
+ Possible values = `string|array`
+ Required = `false`

This represents a readable name for the `column name`. Defaults to `null` and the regular expression - `/[^a-z0-9 -]+/` is used where each valid match is replaced with a space. View example usage below;
```php
"name" => ["label" => "Username"];
"name" => ["label" => "First Name"];
// set to null or ommit the key to use the available defaults
"name" => ["label" => null];
```
When the label key is supplied as an array, the following arguments are expected
+ value = `string` ~ The label value
+ raw = `boolean` ~ true|false, to determine if you can use or not use raw html for the title
Example;
```php
"id" => ["label" => ['value' => '<i class="fa fa-user"></i>&nbsp;User ID', 'raw' => true]],
```
This will render the `id` colum with a label of `<i class="fa fa-user"></i>&nbsp;User ID`. The HTML will be applied too.


### filter
+ Possible values = `array`
+ Required = `false`

This represents a `filter` which will be rendered in a new `<tr>` below the column name. The possible values can be;
+ `boolean` **enabled** - Specifies if the filter is enabled for this column. If not supplied, `false` is assumed.
+ `string` **operator** - Specifies the sql operator that will be applied to the value entered by the user. Possible values are `=`, `like`, etc. Default is `=`. This value is not case sensitive.
+ `string` **type** - specifies the type of filter. Possible values are `text`, `date`, `select` and `daterange`. These are documented on the [filters section](filters.md). Defaults to `text`.
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
The grid comes with about 6 custom css classes that you can add to the columns to alter their widths. They are basically in the form of of `grid-w-x`, where x is a number from 5 to 70. The higher the number, 
the wider your column becomes. However, you are not restricted to these. You can apply your own, as you wish

+ Possible values = `array`
+ Required = `false`

This represents css styles for the grid columns, as well as the rows. possible values can be;
+ `string|callable` **column** - Defines the styles for the `column` of the grid. E.g if you want one of the columns to be have a larger width. Defaults to `grid-w-10`. For a callable, this you may define it;
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
        "column" => "grid-w-10",
        "row" => null
    ],
]
// adding row style as a string
"name" => [
    "styles" => [
        "column" => "grid-w-10",
        "row" => "success"
    ],
]
// adding row style using a function. E.g to highlight the currently logged in user on the grid
// assuming the grid displays a list of users
$loggedInUser = auth()->user();
"name" => [
    "styles" => [
        "column" => "grid-w-10",
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

### sort
+ possible values = `boolean`
+ Required = `false`
+ Defaults to = `true`

Defines if a column would be sorted, when clicked on. Check sample usage below;
```php
"name" => ["sort" => true];
```
> The sort functionality on the grid is both ways. Just click on a column twice to toggle between sorting in ascending and descending order.

### presenter
+ possible values = `string|callable`
+ Required = `false`
+ Defaults to = `null`

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
    "presenter" => function($columnData, $columnName) {
        // do whatever you want to display the data for the `name` column
    }
]
```

### data
+ possible values = `string|callable`
+ required = `false`
+ defaults to = `$data->${column_name}`. E.g `name` will be `$data->name`
```php
"name" => [
    "data" => function($gridItem, $columnName) {
        // $gridItem - column object
        // $columnName - the name of this column (ie, name)
        return $gridItem->{$columnName}
    }
]
```
> The data property should ideally not be used. Please use the `presenter` property to achieve the same results

### date
+ possible values =`boolean`
+ required = `false`
+ defaults to = `false`

Specifies that the column in use is a `date`.
```php
"created_at" => [
    "date" => true,
]
```


### dateFormat
+ possible values =`string`
+ required = `false`
+ defaults to = `Y-m-d`

Allows the formatting of a grid column whose `date` attribute is set to true. E.g
```php
"created_at" => [
    "date" => true,
    "dateFormat" => "l jS \of F Y h:i:s A"
]
```


### raw
+ possible values = `boolean`
+ required = `false`
+ defaults to = `false`.

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
+ possible values = `callable`
+ Required = `false`
+ Defaults to = `null`

Defines a function that would be called to determine if a column would be rendered. Check sample usage below;
```php
// render the column, only if the user is logged in
"name" => [
    "renderIf" => function() {
        return auth()->check();
    }
]
```
> Note that here the callable function cannot use the grid's data because when the columns are rendered the data is not available, since it has to be looped over first.

### export
+ possible values = `boolean`
+ Required = `false`
+ Defaults to = `true`

Defines if a column would be exported, when an option to export is chosen. Check sample usage below;
```php
"name" => ["export" => true];
```