<?php
/**
 * CrossRoads API: JsonClient
 *
 * This is the CrossRoads API JsonClient class.
 * Its purpose is to provide easy json based access to the api
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
 * class JsonClient
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Mike Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */

class CrossRoads_JsonClient
{
    private $username, $password_hash, $creation, $timeout;

    function __construct($key, $secret, $location, $timeout=FALSE) {
        $this->location=$location;
        $this->key=$key;
        $this->secret=$secret;
        $this->creation=time();
    }

    public function __call($name, $args) {
         echo json_encode(array_merge(array('auth'=>array('key'=>$this->key,'nonce'=>$this->nonce(),'creation'=>$this->creation)),array($name=>$args)));
         return $this->doRequest(json_encode(array_merge(array('auth'=>array('key'=>$this->key,'nonce'=>$this->nonce(),'creation'=>$this->creation)),array($name=>$args))));
    }
    
    private function doRequest($request)
    {
            $curl = curl_init($this->location);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_VERBOSE, FALSE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            $response = curl_exec($curl);
            if(curl_errno($curl)) { throw new Exception(curl_error($curl)); }
            curl_close($curl);
			return $response;
    }

    private function nonce() {
        return sha1($this->creation.$this->key.$this->secret);
    }
}
