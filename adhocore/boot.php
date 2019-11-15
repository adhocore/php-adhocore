<?php

namespace Adhocore;

/*
 * Mark the very Onset of our Application
 *
 */
define('ADHOCORE_START', microtime(true));

/*
 * Determine Environment
*/
foreach ($environments as $host => $env) {
    if ((isset($_SERVER['HTTP_HOST']) and $host == $_SERVER['HTTP_HOST'])
            or gethostname() == $host) {
        define('ENVIRONMENT', $env);

        break;
    }
}

/*
 * In case we missed it out!
 */
defined('ENVIRONMENT') or define('ENVIRONMENT', 'development');


/*
 * Now that we define environment, we clear $environments variable
*/
unset($environments);


/*
 * Load defines / constants
*/
require_once APPPATH . 'config/defines.php';


/*
 * We need the helper functions so often
 */
require_once COREPATH . 'functions.php';


/*
 * Register Loader, the AutoLoader
 */
require_once COREPATH . 'Loader.php';
spl_autoload_register(['Adhocore' . NSS . 'Loader', 'load']);

/*
 * Add Library and Models to autoloadable paths
 */
Loader::addPaths([
    APPPATH . 'models',
    APPPATH . 'libraries',
]);

/*
 * Add Core Namespace Directory to the Loader
 */
Loader::addNSDir([
    'Adhocore' => COREPATH,
]);


/*
 * Aliasing all the Core Classes for lazy loading
 */
Loader::addAlias([
    'Autoloader' => join_nss('Adhocore Loader'),
    'Controller' => join_nss('Adhocore Controller'),
    'view' 	 	   => join_nss('Adhocore View'),
    'session' 	  => join_nss('Adhocore Session'),
    'profiler' 	 => join_nss('Adhocore Profiler'),
    'input' 	    => join_nss('Adhocore Input'),
    'database' 	 => join_nss('Adhocore Database'),
    'cookie'	    => join_nss('Adhocore Cookie'),
    'hash'	 	    => join_nss('Adhocore Hash'),
]);

/*
 * Before we jump in, we make sure to load
 * boot.php from the APPPATH, if it exists
 */
if (is_file(APPPATH . 'boot.php')) {
    require_once APPPATH . 'boot.php';
}

/*
 * Create Global instance that Handles the Request
 */
$AHC            = new Adhocore();
$GLOBALS['AHC'] = &$AHC;


/*
 * Init the Handler and Render the Response
 */
ahc()->init();
ahc()->render();

/*
 * Voila! That's All !!
 * --------------------
 */
