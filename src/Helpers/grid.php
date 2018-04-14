<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

if (!function_exists('add_query_param')) {

    /**
     * Add query parameters to a url. Existing ones would be included
     *
     * @return array
     */
    function add_query_param()
    {
        if (func_num_args() === 0) {
            return array_merge(request()->query(), []);
        }

        $values = collect(func_get_args())->collapse()->toArray();

        return array_merge(request()->query(), $values);
    }
}

if (!function_exists('var_export54')) {

    /**
     * Write an array to file, as is
     *
     * https://stackoverflow.com/questions/24316347/how-to-format-var-export-to-php5-4-array-syntax
     *
     * @param $var
     * @param string $indent
     * @return mixed|string
     */
    function var_export54($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export54($key) . " => ")
                        . var_export54($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "true" : "false";
            default:
                return var_export($var, true);
        }
    }
}