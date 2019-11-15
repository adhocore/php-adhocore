<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Loader
{
    private static $nsdir = [];

    private static $maps = [];

    private static $alias = [];

    private static $paths = [];

    public static function load($class)
    {
        if (isset(static::$alias[$class])) {
            return class_alias(static::$alias[$class], $class);
        }

        if (isset(static::$maps[$class])) {
            return require static::$maps[$class];
        }

        foreach (static::$nsdir as $ns => $path) {
            if (str_begins($class, $ns) and $class !== $ns) {
                $class = substr($class, strlen($ns));
                static::addPaths($path);

                break;
            }
        }

        $class = str_replace([NSS, '_'], '/', $class);

        foreach (static::$paths as $dir) {
            if (file_exists($file = $dir . $class . EXT)) {
                return require $file;
            }
        }

        return false;
    }

    public static function hasAlias($alias)
    {
        return array_key_exists($alias, static::$alias);
    }

    public static function isMapped($class)
    {
        return array_key_exists($class, static::$maps);
    }

    public static function addAlias($aliases)
    {
        static::$alias += $aliases;
    }

    public static function addMap($map)
    {
        $map          = array_ensure_append($map, EXT);
        static::$maps = array_merge($map, static::$maps);
    }

    public static function addNSDir($dir)
    {
        static::$nsdir = array_merge($dir, static::$nsdir);
    }

    public static function addPaths($path)
    {
        $path          = array_ensure_append($path, DS);
        static::$paths = array_unique(array_merge($path, static::$paths));
    }
}
