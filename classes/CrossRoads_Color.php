<?php
/**
 * CrossRoads API: Color
 *
 * This is the CrossRoads API color class.
 * Its purpose is to produce ansi standard color output for
 * console/logging.
 * It has no constructor It is called using its methods.
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
 * class Color
 *
 * Any colour you like
 *
 * @category Utilities
 * @package  CrossRoads
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @author   Michael Myles <michael.myles@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 class CrossRoads_Color
 {
	/** method colorize
	*
	* Converts different method calls to colors, or returns plain text
   	*
   	* @param string $color ansi color code
   	* @param string $str string to color
   	*
   	* @return string
   	*/
    private function colorize($color, $str) {
	    $color_arr = array("green"=>'32', "red"=>'31', "white"=>'37', "cyan"=>'36', "brown"=>'33', "magenta"=>'35', "blue"=>'34', "yellow"=>'1;33'); // lets define some colors, mmmm perdy
	    	
    	if(array_key_exists($color, $color_arr)) { 
			return chr(27)."[".$color_arr[$color]."m".$str.chr(27)."[0m"; 
    	} else {
			return $str; 
    	}
    }

	public function __call($color, $str){
		return $this->colorize(strtolower($color), $str);
	}
	
	public static function __callStatic($color, $str) {
		return self::colorize(strtolower($color), $str);
	}
 }
