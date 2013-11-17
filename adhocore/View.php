<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class View {
	
	private $data = array(); 
	
	public function __construct()
	{
		
	}

	public function __get($key)
	{
		return ahc()->{$key};
	}
	
	public function load($view, $data = array(), $return = FALSE)
	{
		$this->data = $data;
		
		$path = '';
		
		if (! is_file($view.EXT)) {
			if (strpos($view, '#') === FALSE) {
				$path = APPPATH.'views'.DS;
			} else {
				$path = Package::path(strstr($view, '#', TRUE), 'views');
				$view = trim(strstr($view, '#'), '#');
			}
		}
		
		if (is_file($path.$view.EXT)) {
			
			ob_start() and extract($data, EXTR_SKIP);
			
			try	{
				eval('?>'.file_get_contents($path.$view.EXT));
			} catch (\Exception $e)	{
				ob_get_clean(); 
				throw $e;
			}
			
			return ($return === TRUE) ? ob_get_clean() : ahc()->response->append_output(ob_get_clean());
		}
		
		throw new \Exception('Error 404: View File Not Found: '.$view);
		
	}
	
	public function nest($view, $data = array())
	{
		return $this->load($view, array_merge($this->data, $data), TRUE);
	}
	
}
