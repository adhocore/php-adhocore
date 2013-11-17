<?php 
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Adhocore {
	
	/**
	 * @var Adhocore\Config
	 */
	public $config;
	
	/**
	 * @var Adhocore\Request
	 */
	public $request;
	
	/**
	 * 
	 * @var Adhocore\Router
	 */
	public $router;
	
	/**
	 * @var Adhocore\Response
	 */
	public $response;
	
	/**
	 * @var Adhocore\Profiler
	 */
	public $profiler;
	
	/**
	 * Stores singleton instances of LazyLoaded Core Classes
	 * @var array
	 */
	private $core = array();
	
	public function __construct()
	{
		
	}
	
	public function __get($key)
	{
		$key  = strtolower($key);
	
		if (Loader::has_alias($key) or Loader::is_mapped($key)) {
			empty($this->core[$key]) and $this->core[$key] = new $key();
			return $this->core[$key];
		}
		
		throw new \Exception("Accessing undefined variable ".__CLASS__."::$key");
	}
	
	public function app_config($key = NULL)
	{
		$array = ($this->config and ($config = $this->config->item('application', NULL))) 
					? $config : require APPPATH.'config'.DS.'application'.EXT;
		
		return (is_null($key)) ? $array : array_get($array, $key);
	}
	
	public function init()
	{
		$this->config 	= new Config();
		$this->request 	= new Request();
		$this->router 	= new Router($this->request->ruri(), $this->request->method());
		$this->response = new Response();
		
		if (is_file($file = APPPATH.'config'.DS.'routes'.EXT)) {
			require $file;
		}
		
		Package::read();
		
		if ($this->app_config('profiler', FALSE) === TRUE) {
			$this->profiler = new Profiler();
		}
	}
	
	public function render()
	{
		$this->router->dispatch();
		return $this->response->render();
	}
	
	public function get_loaded_cores()
	{
		return array_combine(array_keys($this->core), 
					array_map(function ($core) {
						return get_class($core);
					}, 
				$this->core));
	}
	
	public function is_loaded($core)
	{
		return (in_array($core, array_keys(get_class_vars(get_class($this)))) 
					or 
				in_array(strtolower($core), array_keys($this->core))); 
	}
	
	public function load($alias, $FQCN)
	{
		return Loader::addAlias(array(strtolower($alias) => $FQCN));
	}
	
	/**
	 * @return Adhocore\Database
	 */
	public function database()
	{
		return $this->__get(__FUNCTION__);
	}
	
	/**
	 * @return Adhocore\Session
	 */
	public function session()
	{
		return $this->__get(__FUNCTION__);
	}
	
	/**
	 * @return Adhocore\Cookie
	 */
	public function cookie()
	{
		return $this->__get(__FUNCTION__);
	}
	
	/**
	 * @return Adhocore\Input
	 */
	public function input()
	{
		return $this->__get(__FUNCTION__);
	}
	
	/**
	 * @return Adhocore\Hash
	 */
	public function hash()
	{
		return $this->__get(__FUNCTION__);
	}
	
	/**
	 * @return Adhocore\Database\Schema
	 */
	public function schema()
	{
		return $this->database()->schema();
	}
	
	/**
	 * Triggers the Router to run the $handler
	 * @param string|array $handler
	 * @param array $params
	 */
	public function run($handler, $params = array())
	{
		return $this->router->handler($handler, $params);
	}
	
	
}
?>