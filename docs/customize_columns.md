# Customize the grid columns
The grid is simply a bootstrap styled table but highly dynamic in nature. In the sense that the data, columns, filters, etc
are all rendered depending on server side logic that you specify.

A default grid - in this case one for the default `users` model would have an array of columns, like this:
```php
    /**
    * Set the columns to be displayed. Check `docs/customize_columns.md` for more information
    *
    * @return void
    * @throws \Exception if an error occurs during parsing of the data
    */
    public function setColumns()
    {
        $this->columns = [
		    "id" => [
		        "label" => "ID",
		        "filter" => [
		            "enabled" => true,
		            "operator" => "="
		        ],
		        "styles" => [
		            "column" => "col-md-2"
		        ]
		    ],
		    "name" => [
		        "search" => [
		            "enabled" => true
		        ],
		        "filter" => [
		            "enabled" => false,
		            "operator" => "="
		        ]
		    ],
		    "email" => [
		        "search" => [
		            "enabled" => true
		        ],
		        "filter" => [
		            "enabled" => false,
		            "operator" => "="
		        ]
		    ],
		    "created_at" => [
		        "sort" => false,
		        "date" => "true",
		        "filter" => [
		            "enabled" => true,
		            "type" => "date",
		            "operator" => "<="
		        ]
		    ]
		];
    }
```
The number of columns can be as many as you want. Just make sure that they fit within the space you allocate. A listing of each attribute
within the columns array shall be explained below.

## Column name
This is passed as a `key` in the array. Each array key **represents a column that exists** on the table that represents your eloquent model

## Column data
This is the `value` of the array. It has a variety of key value pairs too, which will be explained below;

### sort
+ Possible values = `boolean`
+ Required = `false`

This specifies if a column is sortable. Defaults to `true`

### label
+ Possible values = `string`
+ Required = `false`

This represents a readable name for the `column name`. Defaults to `/[^a-z0-9 -]+/` and each valid match replaced with a space

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

## sort
possible values = `boolean`
Required = `false`

Defines if a column would be sorted, when clicked on. If not provided, this value defaults to `true`
