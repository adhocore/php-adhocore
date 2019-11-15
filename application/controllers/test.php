<?php

class Test extends Adhocore\Controller
{
    public $db;
    
    public function __construct()
    {
        $this->db = ahc()->database();
    }
    
    public function public_index($arg = '')
    {
        echo_([
            'file' 		    => __FILE__,
            'controller' => __CLASS__,
            'method'	    => __FUNCTION__,
        ]);
    }
}
