<?php

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
        $data = $var;
        $dump = var_export($data, true);

        $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
        $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
        $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties

        if (gettype($data) == 'object') { // Deal with object states
            $dump = str_replace('__set_state(array(', '__set_state([', $dump);
            $dump = preg_replace('#\)\)$#', "])", $dump);
        } else {
            $dump = preg_replace('#\)$#', "]", $dump);
        }

        return $dump;
    }
}