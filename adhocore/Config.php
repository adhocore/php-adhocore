<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Config
{
    public $items = [];

    public function __construct()
    {
        $this->items['application'] = ahc()->app_config();
    }

    public function item($key, $fallback = null)
    {
        $base = (strstr($key, '.', true)) ?: $key;

        if (! isset($this->items[$base])) {
            $this->items[$base] = $this->load_config($base);
        }

        return array_get($this->items, $key, $fallback);
    }

    private function load_config($base)
    {
        $packages = Package::all();

        if (substr($base, 0, 1) === '#') {
            $package = str_replace('#', '', $base);
            if (isset($packages[$package])) {
                return $this->read_config($packages[$package] . 'config' . DS . 'package');
            }
        }

        $config = [];

        // First we read the main config, Then, environment based config
        // And finally, the config in each packages, Thus cascading the overall configs
        foreach (array_flatten([APPPATH, APPPATH . 'config' . DS . ENVIRONMENT, $packages]) as $dir) {
            $read   = (str_ends($dir, ENVIRONMENT)) ? $dir . DS . $base : $dir . 'config' . DS . $base;
            $config = array_merge($config, (array) $this->read_config($read));
        }

        return $config;
    }

    private function read_config($file)
    {
        if (strtolower(substr($file, -4)) !== EXT) {
            $file .= EXT;
        }

        return is_file($file) ? require $file : [];
    }

    protected function set_config($config = [])
    {
        if (empty($this->items[$config]) and $config) {
            $this->items[$config] = $config;
        }
    }
}
