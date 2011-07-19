<?php
/**
 * CrossRoads API: SoapClient
 *
 * This is the CrossRoads API SoapClient class.
 * Its purpose is to extend php's soap client to add a proper timeout
 * and soap header based credentials
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
 * class SoapClient
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Mike Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html

 * @link     http://atomicmaster.com
 */

ini_set("soap.wsdl_cache_enabled", "0");
class CrossRoads_SoapClient extends SoapClient
{
    protected $key, $secret,$creation,$timeout;
    function __construct($key,$secret,$wsdl,$timeout=FALSE,$options=array()) {
        if($timeout) { $this->__setTimeout($timeout); }
        $this->key=$key;
        $this->secret=$secret;
        $this->creation=time();
        parent::__construct($wsdl,$options);
        $this->__setSoapHeaders(new SoapHeader('Security','Security',$this->soapHeader(),true));
    }

    public function __setTimeout($timeout) {
        if(!is_int($timeout) && !is_null($timeout)) {
            throw new Exception("Invalid timeout value");
        }
        $this->timeout = $timeout;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = FALSE) {
        if(!$this->timeout) {
            // Call via parent because we require no timeout
            $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        } else {
            // Call via Curl and use the timeout
            $curl = curl_init($location);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_VERBOSE, FALSE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            $response = curl_exec($curl);
            if(curl_errno($curl)) { throw new Exception(curl_error($curl)); }
            curl_close($curl);
        }
        if(!$one_way) { return ($response); }
    }

    protected function nonce() {
        return sha1($this->creation.$this->key.$this->secret);
    }

    protected function soapHeader() {
        $authHeader='<APIAuth><API_KEY>'.
                    $this->key.
                    '</API_KEY><NONCE>'.
                    $this->nonce().
                    '</NONCE><CREATION>'.
                    $this->creation.
                    '</CREATION></APIAuth>';
        return new SoapVar($authHeader,XSD_ANYXML);
    }
}
