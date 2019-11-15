<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

abstract class Controller
{
    public $restful = false;

    public function __get($key)
    {
        return ahc()->{$key};
    }

    public function __call($method, $params)
    {
        return ahc()->{$method}($params);
    }
}
