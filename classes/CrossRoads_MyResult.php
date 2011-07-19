<?php
/**
 * CrossRoads API: MyResult
 *
 * This is the CrossRoads API MyResult class.
 * Its purpose is to return easy to use mysqli query/prepared statement results as arrays
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
 * class MyResult
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Alex Nikitin <alex.nikitin@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
class CrossRoads_MyResult
{
    protected $result, $bind_arr=array(), $row;
    function __construct(&$result) {
        if(!$result || get_class($result)!="mysqli_stmt") {
            throw new CrossRoads_Error("No result set was available.",'NORESULT');
        } else {
            $this->result = $result;
            //this is kind of a dirty but kind of a cool way to solve this issue, but basically this binds array fields to the prepared statement result set to magicify this result class
            if(get_class($result)=="mysqli_stmt") {
                if(!$fields = $this->result->result_metadata()) { throw new CrossRoads_Error("The result contains no data." . $this->result->error(),'NODATA'); }
                if(!$fields = $fields->fetch_fields()) { throw new CrossRoads_Error("Could not fetch fields: " . $this->result->error(),'FETCHERROR'); }
                $bind_cmd = '$this->result->bind_result(';
                foreach($fields as $field)  { $bind_cmd .='$this->bind_arr[\''.$field->name.'\'],'; }
                $bind_cmd = substr($bind_cmd, 0, -1).");";
                eval($bind_cmd);
            }
        }
        $this->row=0;
    }
    function row()  { return $this->row-1; }
	
	//drop will allow you to drop the pre/postfix
    function fetch_array($type=NULL, $prefix=NULL, $postfix=NULL, $join="_", $drop=false) {
        if(get_class($this->result)!="mysqli_stmt") {
            if($this->result->num_rows <= 0) throw new CrossRoads_Error("We need one or more results in the result set first.",'NORESULT');
            switch(strtoupper($type)) {
                case MYSQLI_ASSOC:
                case "MYSQLI_ASSOC":
                    $ret = $this->result->fetch_array(MYSQLI_ASSOC);
                    break;
                case MYSQLI_NUM:
                case "MYSQLI_NUM":
                    $ret = $this->result->fetch_array(MYSQLI_NUM);
                    break;
                default:
                    $ret = $this->result->fetch_array(MYSQLI_BOTH);
            }
        } else {
            if(!$this->result->fetch()) return false;
            while(list($key, $val) = each($this->bind_arr))  { $ret[$key] =  $val; }
            reset($this->bind_arr);
            if(!$type || $type == MYSQLI_NUM || strtoupper($type)=="MYSQLI_NUM") { $ret=array_values($ret); }
        }
        if($prefix || $postfix) {
            if(!$drop) {
                if($prefix=="MYSQLI_ROW")  { $prefix = $this->row; }
                if($postfix=="MYSQLI_ROW")  { $postfix = $this->row; }
                foreach($ret as $key=>$val)  { $ret[(($prefix||$prefix=="0")?$prefix.$join:"").$key.(($postfix||$postfix=="0")?$join.$postfix:"")] = $val; unset($ret[$key]); }
            } else {
                foreach($ret as $key=>$val)  { $ret[preg_replace('/('.(($prefix||$prefix=="0")?$prefix.$join:"").'|'.(($postfix||$postfix=="0")?$join.$postfix:"").')', '',$key)] = $val; unset($ret[$key]); }
            }
        }
        $this->row++;
        return $ret;
    }
    function fetch_all($type=NULL, $prefix=NULL, $postfix=NULL, $join=NULL, $drop=false)
    {
        $tmp=array(); $ret=array(); // it's nice to initilaize arrays
        while($tmp = $this->fetch_array($type,$prefix,$postfix,$join,$drop)) { $ret[]=$tmp; }
        $this->result->free_result(); // Free the original result set (note on change, in mysqli query free is aliased to free_result, always free the result as per the documentation
        return $ret;
    }
}
