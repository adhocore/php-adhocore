<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Cookie
{
    private $collector = [];

    public function __construct()
    {
    }

    public function set($name, $value, $expire = null, $path = '/', $domain = null, $secure = false)
    {
        $name                        = ahc()->input()->sanitize_key($name);
        is_null($expire) and $expire = ahc()->config->item('session.timeout', 5);
        ($expire) and $expire        = time() + ($expire * 60);
        empty($path) and $path       = '/';

        $this->collector[$name] = compact('name', 'value', 'expire', 'path', 'domain', 'secure');
    }

    public function get($name, $default = null)
    {
        if (isset($this->collector[$name])) {
            return $this->collector[$name]['value'];
        }

        return ($data = ahc()->input()->cookie($name)) ? ahc()->hash()->decrypt($data) : $default;
    }

    public function delete($name, $path = '/', $domain = null, $secure = false)
    {
        if ($this->exist($name)) {
            $this->set($name, null, -99, $path, $domain, $secure);
        }
    }

    public function exist($name)
    {
        return isset($this->collector[$name]) or (bool) ahc()->input()->cookie($name);
    }

    public function all()
    {
        return $this->collector;
    }
}
