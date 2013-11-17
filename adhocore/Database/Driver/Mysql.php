<?php
namespace Adhocore\Database\Driver;

defined('COREPATH') or die('Error 403');

class Mysql extends \Adhocore\Database\Driver {
	
	public function __construct()
	{
		$this->name = 'mysql';
		$this->wrapper = '`%s`';
	}
	
	public function connect()
	{
		if ($this->connection === NULL) {
			$config = ahc()->database()->config();
			
			$dsn = "mysql:host=".$config['host'].";dbname=".$config['database']
				 . (($port = $config['port']) ? ";port={$port}" : '');
			
			$this->connection = new \PDO($dsn, $config['user'], 
					$config['pass'], $config['options']
				);
		}
		
		return $this->connection;
	}
	
	public function list_fields($table)
	{
		$result = $this->query("DESCRIBE {$this->wrap($table)};");
		return ($result) ? array_keys(array_indexby($result, 'Field')) : array();
	}
	
	public function list_tables()
	{
		$result = ($this->query('SHOW TABLES;'));
		return ($result) ? array_keys(array_indexby($result, 'Tables_in_'
				. ahc()->database()->config('database'))) : array();
	}
}