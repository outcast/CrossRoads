<?php
/**
 * CrossRoads API: Error
 *
 * This is the CrossRoads API error class.
 * Its purpose is to provide easy error handling
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
 * class Error
 *
 * This is a basic error class.
 *
 * @category Utilities
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html

 * @link     http://atomicmaster.com
 */
 
class CrossRoads_Error extends Exception
{
   public $severity;
   public $internal_code;
   public function __construct($message=NULL,$internal_code='NONE',$severity='alert') {
       $this->severity=$severity;
       $this->internal_code=$internal_code;
       parent::__construct($message,0);
   }
   
   final public function getSeverity() {
       return $this->severity;
   }
   
   public function soap($log=FALSE) {
       if($log) $this->log();
       throw new SoapFault($this->internal_code, $this->message);
   }
   
   public function json($log=FALSE) {
       if($log) { $this->log(); }
       echo json_encode(array('error'=>array('code'=>$this->internal_code,'message'=>$this->message)));	
   }
   
   private function log() {
     $msg = $this->internal_code.": ".$this->message;
     switch($this->severity) {
        case 'info':
            CrossRoads_Log::info($msg);
            break;
        case 'notify':
            CrossRoads_Log::notify($msg);
            break;
        case 'warn':
            CrossRoads_Log::warn($msg);
            break;
        case 'alert':
            CrossRoads_Log::alert($msg);
            break;
        default:
     }
   }
 }
