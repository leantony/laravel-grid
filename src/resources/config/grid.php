<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

/**
 * Config file for the grids
 */
return [

    /**
     * The size in bootstrap grid rows for the grid toolbar
     * By default, 6 for the search input, and 6 for the toolbar buttons
     */
    'toolbar_size' => [6, 6],

    /**
     * Whether to display a message if the grid has no data
     */
    'warn_when_empty' => true,

    /**
     * Default css class for the grid
     */
    'default_class' => 'table table-bordered table-hover',

    /**
     * Configuration related to sorting of data
     */
    'sort' => [
        /**
         * The query parameter supplied to indicate a sort direction
         */
        'dir_param' => 'sort_dir',
    ],

    /**
     * Configuration related to exporting of data
     */
    'export' => [
        /**
         * The query parameter supplied to indicate an export
         */
        'param' => 'export',

        /**
         * Allowed export types
         */
        'allowed_types' => [
            'pdf',
            'xlsx', // excel
            'csv', // csv
            'json',
            'html',
        ],

        /**
         * Export chunk size
         */
        'chunk_size' => 200,

        /**
         * Strict mode - only export columns available on the corresponding DB table
         */
        'strict_mode' => true,
    ],

    /**
     * Configuration related to searching of data
     */
    'search' => [

        /**
         * The search query parameter that contains the search data
         */
        'param' => 'q',

        /**
         * The SQL query type used to conditionally search data
         */
        'query_type' => 'or',

        /**
         * The view used to display a search form
         */
        'view' => 'leantony::grid.search'
    ],

    /**
     * Configuration related to filtering of data
     */
    'filter' => [

        /**
         * The SQL query type used to conditionally filter data
         */
        'query_type' => 'and',

        /**
         * Columns to skip during filtering of user data. This columns
         * will be ignored when passed as query parameters during a filter operation
         */
        'columns_to_skip' => [
            'password',
            'remember_token',
            'activation_code'
        ]
    ],

    /**
     * Configuration related to the grid columns
     */
    'columns' => [

        /**
         * The css class for the grid column with the filter button
         */
        'filter_field_class' => 'grid-w-15',

        /**
         * The regular expression pattern to be used to format column labels
         */
        'label_pattern' => '/[^a-z0-9 -]+/'
    ],

    /**
     * Configuration related to pagination of data
     */
    'pagination' => [

        /**
         * Pagination default size
         */
        'default_size' => 15,

        /**
         * Pagination function to use. Supply either 'default' or 'simple'
         */
        'type' => 'default',

        /**
         * The view used to render default pagination.
         */
        'default' => 'leantony::grid.pagination.default',

        /**
         * The view used to render simple pagination.
         */
        'simple' => 'leantony::grid.pagination.simple'
    ],

    /**
     * Configuration related to grid generation via the provided artisan command
     */
    'generation' => [

        /**
         * Namespace for the generated grid
         */
        'namespace' => 'App\\Grids',

        /**
         * Columns to skip on generation of the grid
         */
        'columns_to_skip' => [
            'password',
            'password_hash',
        ]
    ]
];