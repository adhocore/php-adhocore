<?php

namespace Adhocore\Session\Driver;

defined('COREPATH') or die('Error 403');

class Cookie extends \Adhocore\Session\Driver
{
    public function __construct()
    {
        parent::__construct();
    }

    public function read($item = null)
    {
        if (ahc()->cookie()->exist($name = ahc()->session()->config('cookie', 'ahc_sess_kookie'))) {
            return unserialize(ahc()->cookie()->get($name));
        }

        return ['system' => [], 'user' => []];
    }

    public function write()
    {
        ahc()->cookie()->set(
            ahc()->session()->config('cookie', 'ahc_sess_kookie'),
            serialize($this->data),
            ahc()->session()->config('timeout', 5),
            ahc()->session()->config('path', '/'),
            ahc()->session()->config('domain', '/'),
            ahc()->session()->config('secure', false)
        );
    }

    public function erase($item = null)
    {
        ahc()->cookie()->delete(ahc()->session()->config('cookie', 'ahc_sess_kookie'));
    }
}
