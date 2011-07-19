<?php
/**
 * CrossRoads Index
 *
 * This is the main CrossRoads file.
 * Its purpose is to manage anything that goes through CrossRoads
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

require_once('config.php');     		// overall framework configuration
require_once(WEB_ROOT.'config.php');    // config.php needs to exist per service

if($_GET) {
    $urlparts = explode("/",$_GET['url']);
    $modName = $urlparts[0];
    $modAction = $urlparts[1];
    $modData = $urlparts[2];

    defined('MOD_NAME') || define('MOD_NAME', $modName);
    defined('MOD_ACTION') || define('MOD_ACTION', $modAction);
    defined('MOD_DATA') || define('MOD_DATA', $modData);
    defined('MOD_CLASS')|| define('MOD_CLASS', mb_convert_case(MOD_NAME, MB_CASE_TITLE));
    defined('TARGET_NAMESPACE') || define('TARGET_NAMESPACE', ucwords(SERVICE).'-'.MOD_CLASS);
    
    if(@!include_once(MODULES_DIR.MOD_NAME.DS.MOD_NAME.".php"));
    else {
        $module = new MOD_CLASS();
        try
        {
            $ref = CrossRoads_Reflection::reflect(MOD_NAME.'::'.MOD_ACTION);
        }
        catch (Exception $e) {
            CrossRoads_Log::notify('Tried to access non-existant method of class: '.MOD_NAME);
        }
        if(!$ref->isPrivate()) {
            if(!$ref->isProtected()) {
                 CrossRoads_Log::info('Accessed method: '.MOD_NAME.'::'.MOD_ACTION);
                 $module->$modAction($modData);
            }
            else CrossRoads_Log::warn('Attempted to access Protected method: '.MOD_NAME.'::'.MOD_ACTION);
        }
        else CrossRoads_Log::alert('Attempted to access Private method: '.MOD_NAME.'::'.MOD_ACTION);
    }
}
else { include_once(WEB_ROOT."index.php"); } //serve something


/******* GLOBAL FUNCTIONS *********/
/* class autoloader */
function __autoload($class) {
    $class = (preg_match("|(.*)::.*|", $class, $matches)) ? strtolower($matches[1]) : $class;
    if(@!include_once(SERVICE_CLASSES.$class.".php")) {
        if(@!include_once(MODULES_DIR.$class.DS.$class.".php")) {
            require_once(CLASSES.$class.".php");
        }
    }
}
