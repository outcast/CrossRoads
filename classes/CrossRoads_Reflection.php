<?php
/**
 * CrossRoads API: Reflection
 *
 * This is the CrossRoads API Reflection class.
 * Its purpose is to simplify the use of reflection in php
 * and to aid in the proper exposing of classes as web services.
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

/**
 * class Reflection
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Mike Myles <michael.myles@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */


class CrossRoads_Reflection
{
    /**
    * This is the only method in this class.
    *
    * @param mixed $thing which is an object, a class, a method, or a function
    *
    * @return ReflectionObject|ReflectionClass|ReflectionMethod|ReflectionFunction
    */
    function reflect($thing) {
        if(is_string($thing)) { $meth = explode("::", $thing); }
        if(is_object($thing)) {
            return new ReflectionObject($thing);
        } elseif(class_exists($thing)) {
            return new ReflectionClass($thing);
        } elseif(method_exists($meth[0], $meth[1])) {
            return new ReflectionMethod($thing);
        } elseif(function_exists($thing)) {
            return new ReflectionFunction($thing);
        } else { 
			throw new CrossRoads_Error("Don't you recognize your own reflection?",'REFERROR');
		}
    }
}
