<?php 

defined('COREPATH') or die('Error 403');

class Admin extends Controller 
{
	public function public_index()
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
		));
	}
	
	public function public_add()
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
		));
	}
	
	public function public_edit($id = 0)
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
		));
	}
}