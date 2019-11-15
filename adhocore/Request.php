<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Request
{
    private $method;

    private $headers;

    private $uri;

    private $pathinfo;

    private $baseurl;

    private $host;

    private $ip;

    private $scripturl;

    public function __construct()
    {
        $this->method = strtoupper($this->server('REQUEST_METHOD', 'GET'));
        if ('POST' === $this->method) {
            $this->method = strtoupper($this->header('X-HTTP-METHOD-OVERRIDE', 'POST'));
        }
    }

    public function method()
    {
        return $this->method;
    }

    public function get_env()
    {
        return defined('ENVIRONMENT') ? ENVIRONMENT : 'development';
    }

    public function referrer()
    {
        return $this->header('referer');
    }

    public function headers()
    {
        if ($this->headers === null) {
            $headers = [];
            // Symfony\Component\HttpFoundation\ServerBag
            foreach ($this->server() as $key => $value) {
                $key = $this->format_key($key);
                if (str_begins($key, 'http-')) {
                    $headers[substr($key, 5)] = $value;
                } elseif (in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
                    $headers[$key] = $value;
                }
            }

            $this->headers = $headers;
        }

        return $this->headers;
    }

    public function header($key, $default = null)
    {
        $key = $this->format_key($key);
        if (str_begins($key, 'http-')) {
            $key = substr($key, 5);
        }

        return array_pick($this->headers(), $key, $default);
    }

    public function server($key = null, $default = null)
    {
        return array_pick($_SERVER, ($key) ? strtoupper($key) : null, $default);
    }

    public function server_name()
    {
        return $this->server(__FUNCTION__, 'localhost');
    }

    public function server_port($default = 80)
    {
        return $this->server(__FUNCTION__, $default);
    }

    private function port()
    {
        $port = ($this->is_secure()) ?  $this->server_port('443') : $this->server_port('80');
        if (($this->is_secure() && $port != '443')
                or
            (! $this->is_secure() && $port != '80')) {
            return ':' . $port;
        }

        return '';
    }

    public function user_agent()
    {
        return $this->server('http_user_agent', false);
    }

    public function browser($user_agent = null)
    {
        return get_browser($user_agent, true);
    }

    public function host()
    {
        if ($this->host === null) {
            $this->host = $this->is_secure() ? 'https://' : 'http://'
                . $this->server('HTTP_HOST', $this->server('SERVER_NAME', 'localhost'))
                . $this->port();
        }

        return $this->host;
    }

    public function script_url()
    {
        if ($this->scripturl === null) {
            // YII framework CHttpRequest::getScriptUrl
            $scriptname = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptname) {
                $this->scripturl = $_SERVER['SCRIPT_NAME'];
            } elseif (basename($_SERVER['PHP_SELF']) === $scriptname) {
                $this->scripturl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptname) {
                $this->scripturl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (($pos=strpos($_SERVER['PHP_SELF'], '/' . $scriptname)) !== false) {
                $this->scripturl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptname;
            } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->scripturl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            }
        }

        return $this->scripturl;
    }

    public function ruri()
    {
        return $this->uri_string = preg_replace("|" . preg_quote(ahc()->app_config('url_suffix')) . "$|", "", $this->path_info());
    }

    public function uri()
    {
        if ($this->uri === null) {
            // YII framework CHttpRequest::getRequestUri
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {// IIS
                $this->uri = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->uri = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['HTTP_HOST'])) {
                    if (strpos($this->uri, $_SERVER['HTTP_HOST']) !== false) {
                        $this->uri=preg_replace('/^\w+:\/\/[^\/]+/', '', $this->uri);
                    }
                } else {
                    $this->uri=preg_replace('/^(http|https):\/\/[^\/]+/i', '', $this->uri);
                }
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
                $this->uri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $this->uri .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else {
                throw new \Exception('Cannot detect URI');
            }
        }

        return $this->uri;
    }

    public function path_info()
    {
        if ($this->pathinfo === null) {
            // YII framework CHttpRequest::getPathInfo
            $pathinfo = $this->uri();

            if (($pos = strpos($pathinfo, '?')) !== false) {
                $pathinfo = substr($pathinfo, 0, $pos);
            }

            $scripturl = $this->script_url();
            $baseUrl   = $this->base_url();
            if (strpos($pathinfo, $scripturl) === 0) {
                $pathinfo = substr($pathinfo, strlen($scripturl));
            } elseif ($baseUrl === '' or strpos($pathinfo, $baseUrl) === 0) {
                $pathinfo = substr($pathinfo, strlen($baseUrl));
            } elseif (strpos($_SERVER['PHP_SELF'], $scripturl) === 0) {
                $pathinfo = substr($_SERVER['PHP_SELF'], strlen($scripturl));
            }

            $this->pathinfo = trim($pathinfo, '/');
        }

        return $this->pathinfo;
    }

    public function uri_segment($index = null, $default = null)
    {
        $segments = array_diff(explode('/', trim($this->ruri(), '/')), ['']);
        $key      = is_numeric($index) ? $index - 1 : null;

        return array_pick($segments, $key, $default);
    }

    public function base_url()
    {
        if ($this->baseurl === null) {
            $this->baseurl = $this->host() . rtrim(dirname($this->script_url()), '\\/');
        }

        return $this->baseurl;
    }

    public function is_ajax()
    {
        return $this->header('x-requested-with', '') === 'XMLHttpRequest';
    }

    public function is_secure()
    {
        return 'on' == strtolower($this->server('HTTPS')) or 1 == $this->server('HTTPS');
    }

    private function format_key($key)
    {
        return str_replace('_', '-', strtolower($key));
    }

    public function query()
    {
        return $this->server('QUERY_STRING', '');
    }

    public function ip()
    {
        if ($this->ip === null) {
            foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR'] as $header) {
                if (($spoof = $this->server($header, false)) !== false) {
                    if (strpos($spoof, ',') !== false) {
                        $spoof = explode(',', $spoof, 2);
                        $spoof = $spoof[0];
                    }

                    if (filter_var($spoof, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) or
                        filter_var($spoof, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        break;
                    } else {
                        $spoof = false;
                    }
                }
            }

            $this->ip = ($spoof !== false and $spoof !== '::1') ? $spoof : '0.0.0.0';
        }

        return $this->ip;
    }

    public function accept()
    {
        return $this->header('accept');
    }
}
