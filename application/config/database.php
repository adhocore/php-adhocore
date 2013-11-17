<?php
return array(
	'driver'	=> 'mysql',
	
	'host'      => 'localhost',
	
	'database'  => 'adhocore',
	
	'user'	    => 'root',
	
	'pass'  	=> '',
	
	'port'		=> NULL,
	
	'fetch_style' => PDO::FETCH_CLASS,
	
	'table_prefix'=> '',
	
	'options'	=> array(
		PDO::ATTR_PERSISTENT => TRUE, 
		PDO::ATTR_ERRMODE 	 => PDO::ERRMODE_EXCEPTION,
	),
	
);
