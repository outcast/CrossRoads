<?php
/**
 * CrossRoads API: Crypto
 *
 * This is the CrossRoads API Crypto class.
 * Its purpose is to provide all your crypto needs, hashing and whatnot
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
 * class Crypto
 *
 * @category Utility
 * @package  CrossRoads
 * @author   Alex Nikitin <alex.nikitin@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */

class CrossRoads_Crypto
{
	protected $salt, $data, $iv, $mode, $method,$key;

	// Class constructor
	function __construct($data=NULL, $salt=NULL, $method=NULL, $iv=NULL, $mode=NULL,$key=NULL) {
		$this->setData($data);
		$this->setSalt($salt);
		$this->setIv($iv);
		$this->setMode($mode);
		$this->setMethod($method);
		$this->setKey($key);
	}
	//Destructor
	function __destruct() {
		//cleanup the memory a little bit
		unset($this->salt);
		unset($this->data);
		unset($this->iv);
		unset($this->mode);
		unset($this->method);
		unset($this->key);
	}

	function __toString() { return $this->data; }
	public function getIvSize() { return mcrypt_get_iv_size(constant($this->method), constant($this->mode)); }
	public function setIv($iv=NULL) { $this->iv=$iv; }
	public function getIv() { return $this->iv; }
	public function getData() { return $this->data; }
	public function setSalt($salt=NULL) { $this->salt=$salt; }
	public function setData($data=NULL) { $this->data=$data; }
	public function setKey($key=NULL) { $this->key=$key; }

	/*This will set the method to a supported method by the mcrypt or hash
	* library, agnostic to which one you "plan" on using
	* Please try to use RIJNDAEL_256 as the encryption method (AES256)
	*/
	function setMethod($method=NULL) {
		$method=strtoupper($method);
		$this->method=NULL;
		//check if this is a hash method
		foreach(hash_algos() as $algo) {
			if($method === strtoupper($algo)) {
				$this->method=$method;
				return;
			}
		}
		//if not then check if it's a crypto method
		foreach(mcrypt_list_algorithms() as $supportedMethod) {
			$supportedMethod = strtoupper(str_replace("-","_",$supportedMethod)); //unfortunately the naming conventions seem to be, juuust a little bit different
			if($method===$supportedMethod) {
				$this->method="MCRYPT_".$method;
				return;
			}
		}
		if(!$this->method) { throw new CrossRoads_Error("The cryptographic method \"$method\" is not supported by the system","CRYPTOMETH"); }
	}

	//Set the crypto mode, this is used for en/decryption, generally you'd want to use the ECB mode
	function setMode($mode=NULL) {
		$mode = strtoupper($mode);
		$this->mode=NULL;
		foreach(mcrypt_list_modes() as $supportedMode) {
			if($mode==strtoupper($supportedMode)) { $this->mode="MCRYPT_MODE_".$mode; return; }
		}
		if(!$this->mode) { throw new CrossRoads_Error("The cryptographic mode \"$mode\" is not supported by the system","CRYPTOMODE"); }
	}
	
	function hash() {
		if(!$this->data) { throw new CrossRoads_Error('Hashing requires data to hash','CRYPTOHASH'); }
		if(!$this->method || !preg_match('/^(MHASH)?/',$this->method)) { throw new CrossRoads_Error('Hashing requires a hashing algorithm to hash','HASHALGO'); }
		$this->data = (!$this->salt) ? hash($this->method, $this->data) : hash($this->method, $this->data.$this->salt);
		return;
	}
	
	function encrypt($key=NULL) {	
		if($key) { $this->setKey($key); }
		if(!$this->data) { throw new CrossRoads_Error('Encryption requires data','ENCDATA'); }
		if(!$this->key) { throw new CrossRoads_Error('Encryption requires a key','ENCKEY'); }
		if(!$this->method || !preg_match('/^(MCRYPT)?/',$this->method)) { throw new CrossRoads_Error('Encryption requires an encryption algorithm','ENCALGO'); }
		if(!$this->mode) { throw new CrossRoads_Error('Encryption mode required','ENCMODE'); }
		//generate iv (note it is absolutely necessary to store the iv, valid iv is required at decryption time)
		$this->iv = mcrypt_create_iv($this->getIvSize(), MCRYPT_DEV_URANDOM);
		//Encrypt
		return mcrypt_encrypt(constant($this->method), $this->key, $this->data, constant($this->mode), $this->iv);
	}

	function decrypt($key=NULL) {
		if($key) { $this->setKey($key); }
		if(!$this->data) { throw new CrossRoads_Error('Decrypt requires data.','DECDATA'); }
		if(!$this->key) { throw new CrossRoads_Error('Decryption requires a key','DECKEY'); }
		if(!$this->iv) { throw new CrossRoads_Error('Decryption requires an iv','DECIV'); }
		if(!$this->method || !preg_match('/^(MCRYPT)?/',$this->method)) { throw new CrossRoads_Error('Decryption requires a decryption algorithm','DECALGO'); }
		if(!$this->mode) { throw new CrossRoads_Error('Decryption mode required','DECMODE'); }
		//Decrypt
		return trim(mcrypt_decrypt(constant($this->method), $this->key, $this->data, constant($this->mode), $this->iv));
	}
}
