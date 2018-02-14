# Working with rows on the grid
> A grid displaying a list of users would be used throughout the examples below.

For any grid to be displayed correctly, a list of rows to be displayed need to be provided. This would actually be wrapped in a `<th>` and would appear as the headers for each individual data item.
This is a typical rendering scenario;
```html
<thead>
    <tr>
        @foreach($rows as $row
            <th>{{ row }}</th>
        @endforeach
    </tr>
</thead>
```
So for the rows provided, on the grid, we would loop over each and render the corresponding data.

## configuring rows to display on the grid
The generated grid usually would have a method that looks like this;
Each element in the array serves a purpose and we shall go through each of them below.
```php
 /**
     * Set the rows to be displayed
     *
     * @return void
     * @throws \Exception
     */
    public function setRows()
    {
        $this->rows = [
            'id' => ['sort' => true, 'filter' => 'text'],
            'email' => ['sort' => true, 'filter' => 'text'],
            'name' => ['sort' => true, 'filter' => 'text'],
            'created_at' => ['sort' => true, 'date' => true],
        ];
    }
```
> On the grid generated, by default all rows are sortable, and filterable as text apart from `date` columns.
This implies that those columns would be compared by the `where` eloquent method and using the `=` operator against each of the values. 
Read on and you'll get a feel of how to easily change this as you go on.

## Row items
The array items need to be configured as key value pairs. The `key` is the row name as defined in your Eloquent model. While not really confined to this structure, it is important to define them as such, especially when basic sorting of the row is required.
The value array can be configured as shown below.

### Sort
`sort` specifies if the row is sortable. 
Defaults to `false` if not provided
+ Allows `true or false`
```php
'sort' => 'false'
```

### Filter
`filter` specifies the type of filter to be used on the row. The filter would be rendered as an input element, just below the `<th>` itself.
Defaults to `null` if not provided.
+ Supports `text`, `date`, `select`
```php
'filter' => 'text'
```

### Filter Operator
`filterOperator` is used in tandem with `filter` and specifies the kind of logical operator to be used during filtering. Defaults to `=`
+ values allowed are similar to what eloquent allows in the `where` method.
```php
'filterOperator' => 'like'
```

### Filter Data
`filterData` only applies when the `filter` is of type `select`. The values provided in `filterData` are an array of option values to display on the dropdown input. E.g, assuming you have a grid of users, and you need your grid to filter users by `country_id`.
```php
'filterData' => App\Models\Country::pluck('name', 'id')->toArray();
```
+ defaults to an empty array.

### Filter Custom
`filterCustom` applies on any `filter` type. It defines the strategy to be used when filtering the data. For example, if you have a column named `full Name` that displays a user's `first_name and last_name` on your grid, you can use the following `filterCustom` callback to achieve your goal.

```php
'filter' => 'text', // render a text field for the grid row filter
'filterCustom' => function ($q, $k, $v){
    // q => the query instance
    // k => the row key name
    // v => the value provided by the user
    return $q->where('first_name', 'like', like($v))
            ->orWhere('last_name', 'like', like($v));
}
```
> The `like` function used above is among the helper functions provided by the grid.
It saves the typing of `%` for the like term.

### Date
`date` is used when there is a date column. E.g `created_at`. Data corresponding to this row would be formatted as a date.
```php
'date' => true
```

### Date Format
`dateFormat` only applies when the column is a date column. Just specifies the format to be used when formatting the date.
+ defaults to `Y-m-d`
```php
'dateFormat' => 'Y/m/d H:i:s'
```

### Data
`data` is used to specify the data that would be displayed for each item. 
> If you supply this argument as a static value, then all rows in the grid that correspond to it would have that value.

If you supply a callback, the callback would be specified as follows
```php
'data' => function ($item, $row) {
    return $item->name;
}
```

### Present
`present` is used to specify the presenter function that would be used to render the data. You are free to use any `function name` specified in your presenter if you are using the `laracasts/Presenter` package.
+ presenter function name (as per `laracasts/Presenter`)
+ closure with the following signature. `function ($item, $row)`. Where `$item` is the row item data object. The `$row` is the row object. For example, if our users had a `bio` column, that we needed to display on the grid.

```php
// for laracasts presenter
// the item instance (in this case user) needs a presenter attached to it, and the method that presents the bio needs to be called presentBio
'present' => 'presentBio'

// for custom means of presenting data
'present' => function ($item, $row){
    // for example if you wanted the row have the user bio trimmed to 40 characters
    return str_limit($item->bio, 40);
}
```

### Render If
`renderIf` is used to specify a condition that has to be fulfilled for a row to be displayed on the grid. Its a callback that returns `true or false`. E.g
```php
'renderIf' => function($row){
    // for instance, render a row only when the user is an admin
    return auth()->user()->is_admin;
}
```

### Raw
`raw` is used to specify that a row would be rendered as is as specified by `present`, `data`. For example (see below) if this is the `data`, then `raw` would be set to `true`, so that the html string is not rendered as plain text;
```php
'data' => function ($item, $row) {
    // like for instance, displaying an image on the grid...
    return new HtmlString(sprintf(
                    '<img src="%s" class="img-responsive" alt = "%s" width="40">',
                    asset($item->{$row}), 'alternative'
                ));
    },
'raw' => true, // set raw to true
```
> Hence the item would be rendered in `{!! !!}` as opposed to `{{ }}`

+ defaults to `false`

### Export
`export` defines if the row is to be exported, to the various export options. Takes `true or false`
+ defaults to `true`
> For instance, if your row renders HTML, then this flag should be set to `false` otherwise you'll see HTML tags on your excel sheet.
```php
'export' => true
```