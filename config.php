<?php
/**
 * CrossRoads Config
 *
 * This is the CrossRoads configuration file.
 * Its purpose is to hold any and all necessary configuration information for the framework
 *
 * PHP version 5
 *
 * LICENSE: GPL-2.0
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Mike Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @version  SVN: $Id$
 * @link     http://atomicmaster.com
 */
/* Prior to PHP 5.3, we should fix this */
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));

/* Debugging */
defined('DEBUG') || define('DEBUG', true);                              // for debug backtrace in log messages
defined('SAFE_LOG') || define ('SAFE_LOG', FALSE);                      // for shell-safe logging
defined('COLOR') or define('COLOR', TRUE);                              // for pretty logging messages

/* Core */
defined('DS') || define('DS', '/');                                      // no need for DIRECTORY_SEPERATOR if you just use /
defined('CORE') || define('CORE', __DIR__);                    // core location
defined('ROOT') || define('ROOT', dirname(CORE));           // framework location
defined('CLASSES') || define('CLASSES', CORE.DS.'classes'.DS);           // core classes directory

/* Domains */
defined('SERVICE') || define('SERVICE', $_SERVER['SERVER_NAME']);        // apache needs to be configured properly to use cannonical names for this to work
defined('SERVICE_DIR') || define('SERVICE_DIR', 'services');			 // service directory (in case for some crazy reason someone may want to change it)

defined('WEB_ROOT') || define('WEB_ROOT', SERVICE_DIR.DS.SERVICE.DS);
defined('SERVICE_CLASSES') || define('SERVICE_CLASSES', WEB_ROOT.'classes'.DS);
defined('CONFIG') || define('CONFIG', WEB_ROOT.'config'.DS);             // domain config directory location
defined('MODULES_DIR') || define('MODULES_DIR', WEB_ROOT.'modules'.DS);  // domain module directory

defined('LOG') or define('LOG', CORE.DS.'logs'.DS.SERVICE.'.log');		 // logging structure

/* CrossRoads Services*/
define('CROSSROADS_SOAP', true);
define('CROSSROADS_JSON', true);

/*------- Don't edit below unless you really know what you are doing -------*/
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { define('IP',$_SERVER['HTTP_X_FORWARDED_FOR']); } // we prefer XFF actually, but if its not available, we'll take anything we can get
else if(isset($_SERVER['REMOTE_ADDR'])) { define('IP',$_SERVER['REMOTE_ADDR']); } 
else if(isset($_SERVER['HTTP_CLIENT_IP'])) { define('IP',$_SERVER['HTTP_CLIENT_IP']); }

error_reporting(E_ALL ^ E_NOTICE); // override whatever the admin set to provide the proper amount of error reporting

define('JAVA_DISABLE_AUTOLOAD',TRUE); // disable javabridge autoloader we don't want it, we don't need it... Web is not the place for Java...
