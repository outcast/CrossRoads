<?php
/**
 * CrossRoads API: Memcached
 *
 * This is the CrossRoads API Mysqli class.
 * Its purpose is to create a Mysqli singleton database connection.
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
 * class Memcached
 *
 * @category WebServices
 * @package  Utility
 * @author   Alex Nikitin  <alex.nikitin@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
class CrossRoads_Memcache extends Memcache
{
	public function __construct() {
		if(!(defined('MEMCACHED_SERVERS') && defined('CACHE_PREFIX'))) {
			throw new CrossRoads_Error("Please check config.php for correct memcache settings!",'MEMCONFIG');
		}
		//parent::__construct();
		$servers=explode(",",MEMCACHED_SERVERS);
        foreach ($servers as $server) {
        list($server,$port)=preg_split("/:/",$server);
            if(!($this->addServer($server,$port))) {
                CrossRoads_Log::alert("Could not connect to MemCache server $server:$port");
            }
        }
    }

	public function detonate($array) {
		foreach($array as $key) {
			if(!$this->delete(CACHE_PREFIX.$key)) CrossRoads_Log::alert("Unable to clear cache for: ".CACHE_PREFIX.$key);
		}
	}
}
