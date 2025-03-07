<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Session
{
    private $drivers = [];

    private $driver;

    private $config;

    public function __construct($config = null)
    {
        $this->config = is_array($config) ? $config : ahc()->config->item('session');
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->driver(), $method], $params);
    }

    public function config($item = null, $default = null)
    {
        if (is_null($item)) {
            return $this->config;
        }

        return array_pick($this->config, $item, $default);
    }

    /**
     * @return Adhocore\Session\Driver
     */
    public function driver()
    {
        if (is_null($this->driver)) {
            switch ($this->config('driver')) {
                case 'cookie':
                    $this->driver = new Session\Driver\Cookie();

                    break;

                default:
                    throw new \Exception('Unsupported Session Driver: ' . $driver);
            }
        }

        return $this->driver;
    }
}
