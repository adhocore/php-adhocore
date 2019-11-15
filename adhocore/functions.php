<?php
/**
 *
 * @return Adhocore\Adhocore
 */
function ahc()
{
    return $GLOBALS['AHC'];
}

function route($uri, $route, $method = null)
{
    ahc()->router->route($uri, $route, $method);
}

function join_nss()
{
    return implode(NSS, array_flatten(array_map(function ($k) {
        return str_replace(' ', NSS, $k);
    }, func_get_args())));
}
/*
 * Debug
 */

function die_($data)
{
    die('<pre>' . print_r($data, true) . '</pre>');
}

function echo_($data)
{
    echo('<pre>' . print_r($data, true) . '</pre>');
}


/*
 * Array
 */
function array_ensure_append($array, $append = '')
{
    return array_map(function ($value) use ($append) {
        return rtrim($value, $append) . $append;
    }, (array) $array);
}

function array_ensure_prepend($array, $prepend = '')
{
    return array_map(function ($value) use ($prepend) {
        return $prepend . ltrim($value, $prepend);
    }, (array) $array);
}

function array_flatten(array $array)
{
    $return = [];
    array_walk_recursive(
        $array,
        function ($a) use (&$return) {
            $return[] = $a;
        }
    );

    return $return;
}

function array_pick($array, $key, $default = null)
{
    if (is_null($key) or !is_array($array)) {
        return $array;
    }
    foreach (explode('.', $key) as $segment) {
        if (is_array($array) and array_key_exists($segment, $array)) {
            $array = $array[$segment];
        } else {
            return evaluate($default);
        }
    }

    return $array;
}

function array_get($array, $key = null, $default = null)
{
    return array_pick($array, $key, $default);
}

function array_indexby(array $array, $key = null)
{
    if (! $key or empty($array)) {
        return $array;
    }

    $return = [];
    foreach ($array as $arr) {
        $copy = (array) $arr;
        if (isset($copy[$key])) {
            $return[$copy[$key]] = $arr;
        }
    }

    return $return ?: $array;
}

/*
 * Strings
 */
function str_begins($string, $needle, $care_case = true)
{
    if ($care_case == false) {
        $string =  strtolower($string);
        $needle =  strtolower($needle);
    }

    return strpos($string, $needle) === 0;
}

function str_ends($string, $needle, $care_case = true)
{
    if ($care_case == false) {
        $string =  strtolower($string);
        $needle =  strtolower($needle);
    }

    return strpos(strrev($string), strrev($needle)) === 0;
}

function evaluate($value)
{
    return (is_callable($value) and ! is_string($value))
        ? call_user_func($value) : $value;
}

function stringify($stuff)
{
    if (is_array($stuff) or is_object($stuff)) {
        return serialize($stuff);
    }

    if (is_bool($stuff)) {
        return $stuff ? 'TRUE' : 'FALSE';
    }

    if (is_array($stuff)) {
        return 'NULL';
    }

    return $stuff . '';
}

function file_size($size, $abbr = true)
{
    $units = [
        'B'  => 'Bytes', 'KB' => 'KiloByte', 'MB' => 'MegaByte',
        'GB' => 'GigaByte', 'TB' => 'TeraByte', 'PB' => 'PetaByte', 'EB' => 'ExaByte'
    ];

    // Laravel helpers
    $units = ($abbr) ? array_keys($units) : array_values($units);

    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $units[$i];
}
