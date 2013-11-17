<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Router {
	
	private $uri;
	
	private $method;
	
	private $package;
	
	private $controller;
	
	private $action;
	
	private $routes = array();
	
	private $regex = array(
			'*' 		=> '(.*)',
			'(all)'		=> '(.*)',
			'(alpha)'	=> '([a-zA-Z]+)',
			'(num)'		=> '([0-9]+)',
			'(alnum)'	=> '([a-zA-Z0-9]+)',
			'(any)'		=> '([a-zA-Z0-9\.\-_]+)'
			);
	
	private $optional = array(
			'(:all)'	=> '/?(.*)?',
			'(:alpha)'	=> '/?([a-zA-Z]+)?',
			'(:num)'	=> '/?([0-9]+)?',
			'(:alnum)'	=> '/?([a-zA-Z0-9]+)?',
			'(:any)'	=> '/?([a-zA-Z0-9\.\-_]+)?'
			);
	
	public function __construct($uri, $method)
	{
		if ( trim($uri, '/') and ! preg_match("|^[".ahc()->app_config('uri_chars', 'a-z0-9~_\./\-')."]+$|", $uri)) {
			throw new \Exception('Invalid URI');
		}

		$this->uri = $uri;
		$this->method = $method;
		
		foreach ($this->methods() as $verb) {
			$this->routes[$verb] = array();
		}
		
	}
	
	public function __get($key)
	{
		return (in_array($key, array('uri', 'method', 'package', 'action', 'controller'))) ? $this->{$key} : NULL;
	}
	
	protected function methods()
	{
		return array('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS');
	}
	
	public function route($uri, $route, $method = NULL)
	{
		if (is_array($method) or (is_string($method) and strpos($method, ',') !== FALSE)) {
			if (is_string($method)) {
				$method = array_map('trim', explode(',', $method));
			}
			foreach ($method as $verb) {
				$this->route($uri, $route, $verb);
			}
			return;
		}
		
		if (is_array($uri) or (is_string($uri) and strpos($uri, ',') !== FALSE)) {
			if (is_string($uri)) {
				$uri = array_map('trim', explode(',', $uri));
			}
				
			foreach ($uri as $uri) {
				$this->route($uri, $route, $method);
			}
			return;
		}
		
		if (is_null($method)) {
			foreach ($this->methods() as $verb) {
				if (str_begins($uri, $verb.' ')) {
					$uri = trim(substr($uri, strlen($verb)));
					$method = $verb;
					break;
				}
			}
		}
		
		if (is_null($method)) {
			$method = array('GET', 'POST');
		}
		
		if (array_search($method, $this->methods()) === FALSE) {
			$method = 'GET';
		}
		
		if (is_string($method) and is_string($uri)) {
			$this->routes[$method][$uri] = $route;
		}
			
	}
	
	public function dispatch()
	{
		if (trim($this->uri,'/') == '') {
			return $this->handler(ahc()->app_config('default_route', '404'));
		}
		
		// Route Exact matched URI
		if (isset($this->routes[$this->method][$this->uri])) {
			$handler = $this->routes[$this->method][$this->uri];
			return (is_callable($handler)) ? $this->call($handler) : $this->handler($handler);
			
		}
		
		// Route Regex matched URI
		foreach ($this->routes[$this->method] as $uri => $handler) {
			
			$cache = $uri;
			$matcher = array();
			$uri = str_replace(array_keys($this->regex), array_values($this->regex), $uri);
			$uri = str_replace(array_keys($this->optional), array_values($this->optional), $uri);
			
			if (preg_match('~^'.$uri.(ahc()->app_config('uri_strict_match') === TRUE ? '$':'').'~s', $this->uri, $matcher)) {
				if (is_callable($handler)) 
					return call_user_func($handler);
				
				$handler = str_replace(array_map(function($v) { return '$'.$v; }, 
					array_keys($matcher)), array_values($matcher), $handler);
				$params = ($args = trim(str_replace($matcher[0], '', $this->uri), '/')) ? explode('/', $args) : array();
				
				return $this->handler($handler, $params);
			}
		}

		// Apply Legacy Routing
		if (ahc()->app_config('legacy_routing', TRUE) === TRUE) {
		
			$segments = ahc()->request->uri_segment();
			$suffix = ahc()->app_config('controller_suffix', '');
			$controller = '';
			
			// APPPATH FIRST
			foreach ($segments as $offset => $uri) {
				$suffixed = FALSE;
				$controller .= DS.$uri;
				if (
					is_file(APPPATH.'controllers'.$controller.EXT) // non-suffixed
						or $suffix and
					is_file(APPPATH.'controllers'.$controller.$suffix.EXT) and $suffixed = TRUE
				) {
					return $this->handler(array(
						'package' 		=> NULL,
						'controller' 	=> trim($controller, DS).($suffixed ? $suffix : ''),
						'action'		=> isset($segments[$offset+1]) ? $segments[$offset+1] : NULL
					), @array_slice($segments, $offset + 2), TRUE);
				}
			}
			
			// THEN, PACKAGE PATH			
			$package_path = (Package::exist($segments[0])) ? rtrim(Package::path($segments[0], 'controllers'), DS) : NULL;
			if ($package_path) {
				
				$controller = '';
				$package_controller = '';
				
				foreach ($segments as $offset => $uri) {
					$suffixed = FALSE;
					$controller .= DS.$uri;
					if (isset($segments[$offset+1])) {
						$package_controller .= DS.$segments[$offset+1];
					}
					
					if (
						( 
							(is_file($package_path.$package_controller.EXT) // non-suffixed
								or $suffix and is_file($package_path.$package_controller.$suffix.EXT) and $suffixed = TRUE) 
							and $controller = $package_controller
						) 
							or 
						(
							is_file($package_path.$controller.EXT) // non-suffixed
								or $suffix and
							is_file($package_path.$controller.$suffix.EXT) and $suffixed = TRUE
						)
					) {
						$action = NULL;
						if ($controller == $package_controller) {
							if (isset($segments[$offset+2])) $action = $segments[$offset+2];
						}
						else if(isset($segments[$offset+1])) $action = $segments[$offset+1];
						
						return $this->handler(array(
							'package' 		=> $segments[0],
							'controller' 	=> trim($controller, DS).($suffixed ? $suffix : ''),
							'action'		=> $action,
						), @array_slice($segments, $offset + ($controller == $package_controller ? 3 : 2)), TRUE);
					}
				}
			}
		}
			
		throw new \Exception('Error 404: Cannot Dispatch the Route.');
		
	}
	
	private function controller_exists($path, $controller, $check_suffix = TRUE)
	{
		if ($check_suffix === FALSE) return (is_file($path.$controller.EXT));
		return ($suffix = ahc()->app_config('controller_suffix', '')) ? (is_file($path.$controller.$suffix.EXT)) : FALSE;
	}
	
	public function handler($handler, $params = array(), $exist = FALSE)
	{
		if (is_string($handler)) {
			$handler = str_replace(' ', '', $handler);
		}
		
		$matcher = (is_array($handler)) ? $handler : array();
		$suffix = ahc()->app_config('controller_suffix', '');
		
		if (!empty($matcher) or preg_match('~(?:(?<package>\w+)#)?(?:(?<controller>[\w\/]+))?\.?(?:(?<action>\w+))?~', $handler, $matcher)) {
			
			$action = isset($matcher['action']) ? $matcher['action'] : 'index';
			$controller = str_replace('/', DS, trim(isset($matcher['controller'])?$matcher['controller']:$matcher['package'], '/'));
		
			if (isset($matcher['package']) and Package::exist($matcher['package'])) {
				$this->package = $matcher['package'];
				$filename = Package::path($matcher['package'], 'controllers').$controller;
			} else {
				$filename = APPPATH.'controllers'.DS.$controller;
			}
			
			if ($exist or is_file($filename.EXT) or (is_file(($filename .= $suffix).EXT) and $controller .= $suffix)) {
				
				require $filename.EXT;
				$class = ucfirst(substr($controller, strrpos($controller, DS)));
				
				if ($class and class_exists($class, FALSE)) {
					if (method_exists($class = new $class(),  $action = ($class->restful) ? $action.'_'.strtolower($this->method) : 'public_'.$action)) {
						$this->controller   = $class;
						$this->action		= $action;
						return $this->call($action, $params, $class);
					} else {
						throw new \Exception("Unknown Method: ".get_class($class)."::{$action}()");
					}
				} else {
					throw new \Exception('Controller Not Found: '.$class);
				}
			}
		}
		
		throw new \Exception('Error 404: Cannot Handle the Request.');
	}
	
	protected function call($action, $params = array(), $class = NULL)
	{
		$call = ($class) ? array($class, $action) : $action;
		
		if (ob_get_status()) {
			ahc()->response->append_output(call_user_func_array($call, $params));
			return;
		} else {
			return call_user_func_array($call, $params);
		}
		
		throw new \Exception('Error 404: Cannot process the route.');
	}
	
	 
}
