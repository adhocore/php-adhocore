<?php 
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Loader {
	
	private static $nsdir = array();
	
	private static $maps = array();
	
	private static $alias = array();
	
	private static $paths = array();
	
	static function load($class) 
	{
		
		if (isset(static::$alias[$class])) {
			return class_alias(static::$alias[$class], $class);
		}
	
		if (isset(static::$maps[$class])) {
			return require static::$maps[$class];
		}
		
		foreach (static::$nsdir as $ns => $path) {
			if (str_begins($class, $ns) and $class !== $ns) {
				$class = substr($class, strlen($ns));
				static::addPaths($path);
				break;
			}
		}
		
		$class = str_replace(array(NSS, '_'), '/', $class);
		
		foreach (static::$paths as $dir) {
			if (file_exists($file = $dir.$class.EXT)) {
				return require $file;
			}
		}
		
		return FALSE;
	}
	
	static function has_alias($alias)
	{
		return array_key_exists($alias, static::$alias);
	}
	
	static function is_mapped($class)
	{
		return array_key_exists($class, static::$maps);
	}
	
	static function addAlias($aliases) 
	{
		static::$alias += $aliases; 
	}
	
	static function addMap($map) 
	{
		$map = array_ensure_append($map, EXT);
		static::$maps = array_merge($map, static::$maps);
	}
	
	static function addNSDir($dir) 
	{
		static::$nsdir = array_merge($dir, static::$nsdir);
	}
	
	static function addPaths($path) 
	{
		$path = array_ensure_append($path, DS);
		static::$paths = array_unique(array_merge($path, static::$paths));
	}
}
