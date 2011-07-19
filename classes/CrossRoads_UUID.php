<?php
/**
 * CrossRoads API: UUID
 *
 * This is the CrossRoads API UUID class.
 * Its purpose is to generate Version 4 UUIDs for use in API keys
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
 * class CrossRoads_UUID
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
class CrossRoads_UUID
{
    // method to generate a Version 4 UUID (Psudeo Random)
    function generate($dashes=FALSE) {
        $bits = '';
        while(strlen($bits) < 16) { $bits .= chr(mt_rand(0,255)); }
        $low = bin2hex (substr( $bits, 0, 4 ) );
        $mid = bin2hex (substr( $bits, 4, 2 ) );
        $hi  = (hexdec(bin2hex (substr( $bits, 6, 2 ))) >> 4) | 0x4000;
        $clock = (hexdec(bin2hex ( substr ( $bits, 8, 2 ) )) >> 2) | 0x8000;
        $node = bin2hex ( substr ( $bits, 10, 6 ) );
        return sprintf (($dashes) ? '%08s-%04s-%04x-%04x-%012s' : '%08s%04s%04x%04x%012s', $low, $mid, $hi, $clock, $node );
	}
}
