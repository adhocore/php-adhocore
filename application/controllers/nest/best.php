<?php

defined('COREPATH') or die('Error 403');

class Best extends Controller
{
	public function public_index()
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
			));
	}
}