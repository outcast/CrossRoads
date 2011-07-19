<?php
/**
 * CrossRoads API: WebService
 *
 * This is the CrossRoads API WebService class.
 * Its purpose is to generate web services by exposing the public
 * methods of classes which are extending this one.
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
 * class WebService
 *
 * This is the basic class for defining a web service.
 * It uses the reflection, and doc blocker classes
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
defined('CROSSROADS_SOAP') || define('CROSSROADS_SOAP', false);
defined('CROSSROADS_JSON') || define('CROSSROADS_JSON', false);
 
class CrossRoads_WebService
{
    protected $_ref;
    protected $_user;
    /** constructor */
    function __construct($auth=NULL,$hdr=NULL) {
        $this->_ref = CrossRoads_Reflection::reflect($this);

        /* SOAP */
        if(CROSSROADS_SOAP) {
            //requested something that wasn't a soap call
            if($auth=="NoAuth") {
                $err=new CrossRoads_Error("Security Header missing! Who are you?", "HEADERAUTH");
                return $err->soap(true);
            }
            //requested something that was a soap call
            if($auth!=null) {
                //authentication method goes here.
                if(!(defined('API_KEYS_TABLE'))) {
                    $err=new CrossRoads_Error("Please check config.php for API_KEYS_TABLE settings!","APICONFIG");
                    return $err->soap(true);
            }

                $auth=simplexml_load_string($auth);
                try
                {
                    $stmt=CrossRoads_Mysqli::conn()->prepare('SELECT * FROM `'.API_KEYS_TABLE.'` WHERE `api_key` = ? LIMIT 1');
                    $stmt->bind_param("s", $auth->API_KEY[0]);
                    $stmt->execute();
                    $res = new CrossRoads_MyResult($stmt);
                    $res = $res->fetch_array("MYSQLI_ASSOC");
                    $this->_user=$res['app_name'];
                } catch (CrossRoads_Error $err) {
                    return $err->soap(true);
                }
                if(!$res) {
                    $err= new CrossRoads_Error("Bad Api Key","BADKEY");
                    return $err->soap(true);
                }

                $nonce=sha1($auth->CREATION[0].$res['api_key'].$res['secret']);
                if($nonce != $auth->NONCE[0]) {
                    $err = new CrossRoads_Error("Bad Credentials, That's a bad bad credentials! *whack*","BADAUTH");
                    return $err->soap(true);
                }
                if(DEBUG) {
                    if(preg_match('/:Envelope[^>]*xmlns:([^=]*)="urn:'.strtolower(TARGET_NAMESPACE).'"/', $hdr, $matches)) {
                        $ns=$matches[1];
                        if(preg_match('/<'.$ns.':([^\/> ]*)/', $hdr, $matches)) {
                            $vars = explode('<'.$ns.':'.$matches[1].'>',$hdr);
                            $vars = explode('</'.$ns.':'.$matches[1].'>',$vars[1]);
                            $vars=$vars[0];
                            $vars='<data>'.str_replace(':','_',$vars).'</data>';
                            $vars=simplexml_load_string($vars);
                            $in='';
                            $len=count($vars[0]);
                            foreach($vars[0] as $var=>$val) {
                                $in.="$var=>$val";
                                if($len--> 1) $in.=', ';
                            }
                            CrossRoads_Log::notify('['.$res['app_name'].']'.$this->_ref->name.'->'.$matches[1]."($in)");
                        }
                    }
                }
            }
        }
    }

    /** figure out which methods belong to the child class
    *
    * @return mixed
    */
    function ownMethods() {
        //vulgar display of power...
        $methods=(get_parent_class($this))? array_diff(get_class_methods($this), get_class_methods(get_parent_class($this))):get_class_methods($this);
        return $methods;
    }

    /** wsdl builder
    *
    * @return string
    */
    function wsdl() {
       if(!CROSSROADS_SOAP) die ("SOAP not enabled!");
       new CrossRoads_WSDL($this);
    }

    /** soap service builder
    *
    * @return string
    */
    function soap() {
        if(!CROSSROADS_SOAP) die ("SOAP not enabled!");
        $hdr = file_get_contents("php://input");
        if(strpos($hdr,'<SOAP-ENV:Header>')===false) {
            $auth = "NoAuth";
        }
        else {
            $auth = explode('<SOAP-ENV:Header>',$hdr);
            $auth = explode('</SOAP-ENV:Header>',$auth[1]);
            $auth = $auth[0];
        }
        //CrossRoads_Log::notify(var_export($hdr,true));
        $loc = "https://".$_SERVER['HTTP_HOST'].DS.MOD_NAME.DS."wsdl";
        try
        {
            $server=new SoapServer($loc);
            $server->setClass($this->_ref->name,$auth,$hdr);
            $server->handle();
        } catch (CrossRoads_Error $err) {
            return $err->soap(true);
        }
    }
    /** json service builder
    *
    * @return string
    */
    function json() {
        if(!CROSSROADS_JSON) die ("JSON not enabled!");
        if(!(defined('API_KEYS_TABLE'))) {
            $err= new CrossRoads_Error("Please check config.php for API_KEYS_TABLE settings!","APICONFIG");
            return $err->json(true);
        }

        $data=file_get_contents("php://input");
	//$data=urldecode($data);
	$json= json_decode($data,true);
	CrossRoads_Log::warn(var_export($data,true));
        if(!$json['auth']) {
            $err= new CrossRoads_Error("You're missing an auth portion!", "BADAUTH");
            return $err->json(true);
        }
        try
        {
            $stmt=CrossRoads_Mysqli::conn()->prepare('SELECT * FROM `'.API_KEYS_TABLE.'` WHERE `api_key` = ? LIMIT 1');
            $stmt->bind_param("s", $json['auth']['key']);
            $stmt->execute();
            $res = new CrossRoads_MyResult($stmt);
            $res = $res->fetch_array("MYSQLI_ASSOC");
            $this->_user=$res['app_name'];
            $stmt->close();
        } catch (CrossRoads_Error $err) {
            return $err->json(true);
        }
        if(!$res) {
            $err = new CrossRoads_Error("Bad Credentials, That's a bad bad credentials! *whack*","BADCRED"); //user does not exist
            return $err->json();
        }
        $nonce=sha1($json['auth']['creation'].$res['api_key'].$res['secret']);

        if($nonce != $json['auth']['nonce']) {
            $err = new CrossRoads_Error("Bad Credentials, That's a bad bad credentials! *whack*","BADCRED"); //nonce is wrong
            return $err->json();
        }
        //in theory you can call a bunch of functions at once and get what they return
        unset($json['auth']);
        $funcRes=array();
        try
        {
            foreach($json as $func=>$args) {
                $funcRes[$func]=call_user_func_array(array(&$this,$func),$args);
            }
        } catch (CrossRoads_Error $err) {
            return $err->json(true);
        }
		//CrossRoads_Log::alert(json_encode($funcRes));
        header('Content-Type: application/json');
        header('Content-Length: ' .strlen(json_encode($funcRes))); 
        echo json_encode($funcRes);
    }
}
