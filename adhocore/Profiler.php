<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Profiler {
	
	private $markers = array();
	
	public function __construct()
	{
		
	}
	
	public function start_timer($name)
	{
		if (! isset($this->markers[$name])) {
			$this->markers[$name]['start'] = microtime(TRUE);
		}
	}
	
	public function end_timer($name)
	{
		if (isset($this->markers[$name])) {
			$this->markers[$name]['end'] = microtime(TRUE);
		}
	}
	
	public function overview()
	{
		$overview = array();
	
		$overview['memory'] = file_size(memory_get_usage(true));
		$overview['peak_memory'] = file_size(memory_get_peak_usage(true));
		$overview['elapsed_time'] = number_format((microtime(true) - ADHOCORE_START) * 1000, 2);
		
		foreach ($this->markers as $name => $time) {
			$overview['timers'][$name] = number_format(((isset($time['end']) ? $time['end'] : microtime(TRUE)) - $time['start']) * 1000, 2);
		}
		
		return ahc()->view->load(APPPATH.'views'.DS.'profiler', $overview, TRUE);
	}
}