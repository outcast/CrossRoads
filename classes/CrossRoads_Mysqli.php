<?php
/**
 * CrossRoads API: Mysqli
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
 * class Mysqli
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html

 * @link     http://atomicmaster.com
 */
class CrossRoads_Mysqli extends mysqli
{
    protected static $mysqli;
    private function __construct() {
       parent::__construct(MYSQLI_HOST, MYSQLI_USER, MYSQLI_PASS, MYSQLI_DB, MYSQLI_PORT);
       if(mysqli_connect_errno()) {
           throw new CrossRoads_Error("Could not connect to database! Repent! The end is neigh!",'DBCONN');
       }
    }
    public static function conn() {
        if( !self::$mysqli ) {
			if(!(defined('MYSQLI_USER') && defined('MYSQLI_PASS') && defined('MYSQLI_HOST') && defined('MYSQLI_DB') && defined('MYSQLI_PORT'))) {
                throw new CrossRoads_Error("Please check config.php for correct database settings!",'DBCONFIG');
            }
            self::$mysqli = new self();
        }
        return self::$mysqli;
    }
    public function __clone() {
        trigger_error("Can't clone Mysqli!", E_USER_ERROR);
    }
    public function binder($stmt,$types,$params) {
		if(!is_array($types)) $types=array($types);
		$pos=0;
		foreach($params as $param=>$val) {
			if( $pos = strpos($stmt,'?',$pos+1)) {
				if(empty($val)) {
					if(!is_numeric($val)) {
						$stmt=substr_replace($stmt,'NULL',$pos,1);
						unset($params[$param]);
						$types[0]=substr_replace($types[0],'?',$param,1);
					}
				 }
			 }
		}
		$types[0]=str_replace('?','',$types[0]);
		$prepared=$this->prepare($stmt);
		call_user_func_array(array($prepared,'bind_param'),array_merge($types,$params));
		return $prepared;
    }
}
