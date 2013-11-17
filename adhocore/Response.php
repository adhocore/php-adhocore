<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Response {
	
	private $output = '';
	
	private $status = 200;
	
	private $headers = array();
	
	private $protocol = '1.0';
	
	private $stati = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',            // RFC2518
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',          // RFC4918
		208 => 'Already Reported',      // RFC5842
		226 => 'IM Used',               // RFC3229
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',    // RFC-reschke-http-status-308-07
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',                                               // RFC2324
		422 => 'Unprocessable Entity',                                        // RFC4918
		423 => 'Locked',                                                      // RFC4918
		424 => 'Failed Dependency',                                           // RFC4918
		425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
		426 => 'Upgrade Required',                                            // RFC2817
		428 => 'Precondition Required',                                       // RFC6585
		429 => 'Too Many Requests',                                           // RFC6585
		431 => 'Request Header Fields Too Large',                             // RFC6585
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
		507 => 'Insufficient Storage',                                        // RFC4918
		508 => 'Loop Detected',                                               // RFC5842
		510 => 'Not Extended',                                                // RFC2774
		511 => 'Network Authentication Required',                             // RFC6585
	);
	
	public function __construct()
	{
		
	}
	
	public function header($name, $value)
	{
		$this->headers[] = compact($name, $value);
	
		return $this;
	}
	
	public function set_headers(array $headers = array())
	{
		($headers) and $this->headers = (array) $headers;
		return $this;
	}
	
	public function get_headers()
	{
		return $this->headers;
	}
	
	public function render()
	{
		if ('HTTP/1.1' == ahc()->request->server('SERVER_PROTOCOL', 'HTTP/1.0')) {
			$this->protocol = '1.1';
		}

		$this->headers();
		$this->cookies();
		
		if (ahc()->app_config('profiler') === TRUE and ahc()->request->is_ajax() === FALSE) {
			$this->output .= PHP_EOL.ahc()->profiler->overview();
		}
		
		echo (string) $this->output;
	}
	
	private function headers()
    {
        if (! headers_sent()) {
	        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->stati[$this->status]));
	        foreach ($this->headers as $name => $values) {
	            foreach ($values as $value) {
	                header($name.': '.$value, false);
	            }
	        }
        }
    }
    
    public function cookies()
    {
    	if (ahc()->is_loaded('cookie')) {
    		if (ahc()->is_loaded('session')) {
    			ahc()->session()->driver()->write();
    		}
    		
    		foreach (ahc()->cookie()->all() as $cookie) {
    			extract($cookie, EXTR_OVERWRITE);
    			if ((! $secure and ! ahc()->request->is_secure()) or ($secure and ahc()->request->is_secure())) {
    				$value = ahc()->hash()->encrypt($value);
    				setcookie($name, $value, $expire, $path, $domain, $secure);
    			}
    		}
    	}
    }
	
	public function __toString()
	{
		return $this->render();
	}
	
	public function set_output($output)
	{
		$this->output = $output;
		return $this;
	}
	
	public function get_output()
	{
		return $this->output;
	}
	
	public function append_output($more_output = '')
	{
		($more_output) and $this->output .= $more_output;
		return $this;
	}
	
	public function error($message, $type = '')
	{
		
	}
}
