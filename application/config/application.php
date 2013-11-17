<?php
return array(
	
	'base_url'  		=> '',
	
	'uri_chars' 		=> 'a-z0-9/~_\./-',
	
	'asset_url' 		=> 'assets',
	
	'url_suffix'		=> '.html',
	
	'default_route' 	=> 'test',
	
	'legacy_routing'	=> TRUE,
	
	'uri_strict_match'	=> FALSE,
	
	'crypt_key'			=> '123456',
	
	'controller_suffix'	=> '_controller',
	
	'package_path' 		=> array(
			APPPATH.'packages'.DS, 
		),
	
	'profiler'			=> TRUE,
	
	);