<?php
/**
 * CrossRoads API: Log
 *
 * This is the CrossRoads API log class.
 * Its purpose is to dump information to log files.
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
 * class Log
 *
 * This is a basic error logging class.
 *
 * @category Utilities
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
 class CrossRoads_Log
 {

    /** method trace
    *
    * @return string
    */
     private function trace() {
         if(DEBUG) {
            $trace = debug_backtrace();
            $file =($trace[2]['file'])?$trace[2]['file']:$trace[1]['file'];
            $file = explode('/',$file);
            $file = end($file);
            $line = ' Line '.(($trace[2]['line'])?$trace[2]['line']:$trace[1]['line']);
            $function =(($trace[2]['function'])?$trace[2]['function'].'() ':NULL);
            $msg .=  $function.$file.$line.': ';
            return ($trace[2]['class'])? $trace[2]['class'].'->'.$msg:$msg;
        }
        else return NULL;
     }

    /** method log
   *
   * @param string $type type of log message
   * @param string $str string to log
   *
   * @return void
   */
    private function log($type, $str) {
        $str = '['.date('D M d H:i:s Y').'] '.str_pad('['.$type.']',7).' [client '.IP.'] '.$caller['function'].$caller['class'].$str;
        $fd = fopen(LOG,'a');
        if(defined('SAFE_LOG')) { fwrite ($fd,escapeshellcmd($str)."\n"); }
        else { fwrite($fd,"$str\n"); }
        fclose($fd);
    }

   /** info: standard operational message, lowest level notification
   *
   * @param string $str sting to log
   *
   * @return void
   */
    function info($str) {
        $str = self::trace().$str;
        (COLOR) ? self::log('info', CrossRoads_Color::white($str)):self::log('info',$str);
    }

   /** notify: low level notification
   *
   * @param string $str sting to log
   *
   * @return void
   */
    function notify($str) {
        $str = self::trace().$str;
        (COLOR) ? self::log('note', CrossRoads_Color::green($str)):self::log('note',$str);
    }

   /** warn: medium level notification
   *
   * @param string $str sting to log
   *
   * @return void
   */
    function warn($str) {
        $str = self::trace().$str;
        (COLOR) ? self::log('warn',CrossRoads_Color::yellow($str)):self::log('warn',$str);
    }

   /** alert: high level notification
   *
   * @param string $str sting to log
   *
   * @return void
   */
    function alert($str) {
        $str = self::trace().$str;
        (COLOR) ? self::log('alert',CrossRoads_Color::red($str)):self::log('alert',$str);
    }
 }
