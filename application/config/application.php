<?php

return [

    'base_url'  		=> '',

    'uri_chars' 		=> 'a-z0-9/~_\./-',

    'asset_url' 		=> 'assets',

    'url_suffix'		=> '.html',

    'default_route' 	=> 'test',

    'legacy_routing'	=> true,

    'uri_strict_match'	=> false,

    'crypt_key'			=> '123456',

    'controller_suffix'	=> '_controller',

    'package_path' 		=> [
        APPPATH . 'packages' . DS,
    ],

    'profiler'			=> true,

];
