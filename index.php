<?php 

/*
 * Environments
 * Simply tell what HTTP_HOST is matched to which environment
 */
$environments = array(
		'localhost'		=> 'development',
		'127.0.0.1'	=> 'production',
	);


/*
 * Define the Core constants and paths
 */
define('DS', DIRECTORY_SEPARATOR); 		// DIRECTORY_SEPARATOR
define('NSS', '\\'); 					// NAMESPACE_SEPARATOR
define('EXT', '.php'); 					// PHP EXTENSION
define('ROOTPATH', getcwd());			// SITE ROOT PATH
define('APPPATH', ROOTPATH.DS.'application'.DS);	// APPLICATION PATH
define('COREPATH', ROOTPATH.DS.'adhocore'.DS);		// FRAMEWORK CORE PATH
//define('PLUGINPATH', ROOTPATH.DS.'plugins'.DS);		// THIRD PARTY PLUGINS PATH
define('PLUGINPATH', ROOTPATH.DS.'..'.DS.'plugins'.DS);	// THIRD PARTY PLUGINS PATH

/*
 * Load the bootstrap ... 
 */
require_once COREPATH.'boot.php';
