<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Input {

	private $get 		= array();
	
	private $post 		= array();
	
	private $cookie 	= array();
	
	private $file 		= array();

	public function __construct() 
	{
		// Copy the sanitized array of globals to our cache
		// However, it does not sanitize globals themselves  
		$globals = array(
					'get'		=> $_GET, 
					'post'		=> $_POST, 
					'cookie'	=> $_COOKIE,
					'file'		=> $_FILES,
				);
	
		foreach ($globals as $key => $global) {
			$this->{$key} = $this->sanitize_array($global, FALSE);
		}
		
	}

	public function sanitize_array($array, $STRIP = FALSE) 
	{
		$return = array();
		if (count($array)) {
			foreach ($array as $key => $value) {
				$return[$this->sanitize_key($key)] = (is_array($value) and (count($value)))
					? $this->sanitize_array($value, $STRIP)
					: $this->sanitize_value($value, $STRIP);
			}
		}

		return $return;
	}

	public function sanitize_key($key) 
	{
		return preg_replace('~[^a-z0-9:_\.-]~', '', $key);
	}

	public function sanitize_value($value, $STRIP = FALSE) 
	{
		return $value;
		if (function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		
		$value = preg_replace('#([\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+|%0[0-8bcef]|%1[0-9a-f])#S', '', $value);
		$value = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $value);

		return $STRIP === TRUE ? strip_tags($value) : $value;
	}
	
	public function post($key = NULL, $default = NULL) 
	{
		return array_pick($this->post, $key, $default);
	}
	
	public function get($key = NULL, $default = NULL) 
	{
		return array_pick($this->get, $key, $default);
	}
	
	public function get_post($key)
	{
		return array_pick($this->get + $this->post, $key);
	}
	
	public function post_get($key)
	{
		return array_pick($this->post + $this->get, $key);
	}
	
	public function request($key = NULL, $default = NULL) 
	{
		return array_pick($this->request, $key, $default);
	}
	
	public function file($key = NULL, $default = NULL) 
	{
		return array_pick($this->file, $key, $default);
	}
	
	public function cookie($key = NULL, $default = NULL) 
	{
		return array_pick($this->cookie, $key, $default);
	}

}