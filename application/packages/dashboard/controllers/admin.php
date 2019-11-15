<?php

defined('COREPATH') or die('Error 403');

class Admin extends Controller
{
    public function public_index()
    {
        echo_([
            'file' 		    => __FILE__,
            'controller' => __CLASS__,
            'method'	    => __FUNCTION__,
        ]);
    }
    
    public function public_news()
    {
        echo_([
            'file' 		    => __FILE__,
            'controller' => __CLASS__,
            'method'	    => __FUNCTION__,
        ]);
    }
}
