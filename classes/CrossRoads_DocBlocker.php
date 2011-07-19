<?php
/**
 * CrossRoads API: DocBlocker
 *
 * This is the CrossRoads API "DocBlocker" class.
 * Its purpose is to parse properly formatted docblocks through
 * reflection to aid in the proper exposing of classes as web services.
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
 * class DocBlocker
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */

class CrossRoads_DocBlocker
{
    /**
    * getInfo Method
    *
    * @param ReflectionMethod $method reflection for method
    *
    * @return array
    */
    function getInfo($method) {
        if($docBlock=$method->getDocComment()) {
            $info = array("method"=>$method->name,"@param"=>array(),"@return"=>array(),"@author"=>array(),"@internal"=>array(),"description"=>NULL);
            foreach (explode("\n", $docBlock) as $comment) {
                switch($comment) {
					case (preg_match("|@param\s+([a-zA-Z0-9\[\]]+)\s+\\\$([a-zA-Z0-9]+)\s*(.*)|", $comment, $matches))?$comment:!$comment;
							$info['@param'][] = $matches;
						break;
					case (preg_match("|@return\s+([a-zA-Z0-9\[\]]+)\s*(.*)|", $comment, $matches))?$comment:!$comment;
							$info['@return'][] = $matches;
						break;
					case (preg_match("|@author\s*(.*)|", $comment, $matches))?$comment:!$comment;
							$info['@author'][] = $matches;
						break;
					case (preg_match("|@internal\s*(.*)|", $comment, $matches))?$comment:!$comment;
							$info['@internal'][] = $matches;
						break;
					default:
                        $info['description'].=preg_replace("/[*\\\\\/]/", "", $comment);
                }
            }
            return $info;
        } else {
			return false;
		}
    }
}
