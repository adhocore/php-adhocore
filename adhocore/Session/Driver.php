<?php
namespace Adhocore\Session;

defined('COREPATH') or die('Error 403');

abstract class Driver {
	
	protected $driver;
	
	protected $config;
	
	protected $data;
	
	public function __construct()
	{
		is_null($this->data) and $this->data = $this->read();
	}
	
	abstract public function read();
		
	abstract public function write();
		
	abstract public function erase();
	
	public function set($key, $value)
	{
		$this->data['user'][$key] = $value;
	}
	
	public function get($key, $default = NULL)
	{
		return array_pick($this->data['user'], $key, $default);
	}
	
	public function un_set($key)
	{
		if (array_key_exists($key, $this->data['user'])) {
			unset($this->data['user'][$key]);
		}
	}
	
	public function sess_id()
	{
		return array_pick($this->data['system'], __FUNCTION__, session_id());
	}
	
	public function user_ip()
	{
		return array_pick($this->data['system'], __FUNCTION__, '0.0.0.0');
	}
	
	public function csrf_token()
	{
		return array_pick($this->data['system'], __FUNCTION__, '');
	}
	
	public function last_activity()
	{
		return array_pick($this->data['system'], __FUNCTION__, '');
	}
}