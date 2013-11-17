<?php
namespace Adhocore\Database;

defined('COREPATH') or die('Error 403');

abstract class Driver {
	
	protected $name;
	
	protected $wrapper;
	
	/**
	 * @var \PDO
	 */
	protected $connection;
	
	abstract public function connect();
	
	abstract public function list_fields($table);
	
	abstract public function list_tables();
	
	public function is_conected()
	{
		return (is_null($this->connection) === FALSE);
	}
	
	public function query($sql, $bind="", $fetch_style = NULL)
	{
		$bind = (is_array($bind)) ? $bind : (empty($bind) ? array() : array($bind));
		$sql  = trim($sql);

		try {
			
			$pdoStatement = $this->connection->prepare($sql);
				
			if($pdoStatement->execute($bind) !== FALSE) {
				ahc()->database()->queries(array(
					'sql' 	=> $sql,
					'bind' 	=> ($bind)?json_encode($bind):''
				));
	
				if (preg_match("/^(" . implode("|", array("select", "describe", "pragma", "show")) . ") /i", $sql)) {
					if (is_null($fetch_style)) {
						$fetch_style = ahc()->database()->config('fetch_style', 2);
					}
					return $pdoStatement->fetchAll($fetch_style);
				}
				
				if (preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $sql)) {
					return $pdoStatement->rowCount();
				}
			}
			
		} catch (\PDOException $e) {
			ahc()->database()->errors(array(
				'sql' 	=> $sql,
				'bind' 	=> ($bind)?json_encode($bind):'',
				'error' => $e->getMessage()
			));
				
			throw $e;
			return FALSE;
		}
	}
	
	public function wrapper()
	{
		return $this->wrapper;
	}
	
	public function insert_id()
	{
		return $this->connection->lastInsertId();
	}
	
	public function wrap($field){
		
		if (is_array($field)) {
			return array_map(array($this, 'wrap'), $field);
		}
		
		$field = trim($field);
		
		if (stripos($field, ' as ') !== FALSE) {
			list($column, , $as_column) = explode(' ', $field, 3);
			return $this->wrap($column).' AS '.$this->wrap($as_column);
		}
		
		if (stripos($field, '.') !== FALSE) {
			list($alias, $column) = explode('.', $field);
			return $this->wrap($alias).'.'.$this->wrap($column);
		}
		
		return ($field === '*') ? $field : sprintf($this->wrapper(), $field);
	}
	
	public function wrap_table($table)
	{
		return $this->wrap(ahc()->database()->config('table_prefix').$table);
	}
	
	public function __toString()
	{
		return $this->name;
	}
}