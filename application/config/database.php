<?php

return [
    'driver'	=> 'mysql',
    
    'host'      => 'localhost',
    
    'database'  => 'adhocore',
    
    'user'	    => 'root',
    
    'pass'  	=> '',
    
    'port'		=> null,
    
    'fetch_style' => PDO::FETCH_CLASS,
    
    'table_prefix'=> '',
    
    'options'	=> [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE 	  => PDO::ERRMODE_EXCEPTION,
    ],

];
