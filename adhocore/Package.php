<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Package
{
    private static $packages = [];

    public static function read()
    {
        $packages = [];
        foreach (ahc()->config->item('application.package_path') as $directory) {
            $directory = rtrim($directory, DS) . DS;
            foreach (glob($directory . '*', GLOB_ONLYDIR) as $package) {
                $name = substr(strrchr($package, DS), 1);
                if (! isset(static::$packages[$name])) {
                    $packages[$name] = $package . DS;
                    if (is_file($file = $package . 'boot' . EXT)) {
                        require_once $file;
                    }
                }
            }
        }

        static::$packages = array_merge($packages, static::$packages);
    }

    public static function names()
    {
        return array_keys(static::all());
    }

    public static function all()
    {
        if (empty(static::$packages)) {
            static::read();
        }

        return static::$packages;
    }

    public static function path($name, $subpath = '')
    {
        ($subpath) and $subpath = rtrim($subpath, DS) . DS;

        return static::exist($name) ? static::$packages[$name] . $subpath : false;
    }

    public static function exist($name)
    {
        return isset(static::$packages[$name]);
    }
}
