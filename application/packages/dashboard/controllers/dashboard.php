<?php 

defined('COREPATH') or die('Error 403');

class Dashboard extends Controller 
{
	public function public_index()
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
		));
	}
	
	public function public_news()
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
		));
		
	}
}