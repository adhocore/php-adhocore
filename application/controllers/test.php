<?php 

class Test extends Adhocore\Controller {
	
	var $db;
	
	public function __construct()
	{
		$this->db = ahc()->database();
	} 
	
	public function public_index($arg = '')
	{
		echo_(array(
			'file' 		 => __FILE__,
			'controller' => __CLASS__,
			'method'	 => __FUNCTION__, 
			));
	}
	
}

?>