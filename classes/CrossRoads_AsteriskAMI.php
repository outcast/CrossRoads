<?php
/**
 * CrossRoads API: AsteriskAMI
 *
 * This is the CrossRoads API AsteriskAMI class.
 * Its purpose is to create an easy interface to an Asterisk Management Interface.
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
 * class AsteriskAMI
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html

 * @link     http://atomicmaster.com
 */
 
class CrossRoads_AsteriskAMI
{
    private $_socket;  //socket for connection

    public function __construct() {
		if(!(defined('ASTERISK_SERVERS') && defined('AMI_USER') && defined('AMI_PASS') && defined('AMI_PORT'))) {
			throw new CrossRoads_Error("Please check config.php for correct Asterisk settings!","ASTCONFIG");
		}
		if($this->connect() && $this->login()) { return; }
		throw new CrossRoads_Error("Asterisk connection error!", "ASTCONN");
		exit;
    }

    public function __destruct() {
            $this->logout();
            $this->disconnect();
    }

   /**
   * Connect
   */
    public function connect() {
        if(!$this->_socket) {
            foreach ( explode(',', ASTERISK_SERVERS) as $server) {
                if($this->_socket = fsockopen($server, AMI_PORT)) {
                    stream_set_timeout($this->_socket, 3);
                    return true;
                }
            }
        }
        return false;
    }

   /**
   *  login
   */
    public function login() {
        if(!$this->_socket) return false;
        if( defined('AMI_AUTH')) {  //we're using MD5 auth
            $response = $this->command("Action: Challenge\r\nAuthType: MD5\r\n\r\n");
            if(strpos($response, "Response: Success") !== false) {
                $challenge = trim(substr($response,strpos($response, "Challenge: ")));
                $md5 = md5($challenge . AMI_PASS);
                $response = $this->command("Action: Login\r\nAuthType: MD5\r\nUsername: ".AMI_USER."\r\nKey: {$md5}\r\n\r\n");
            } else {
                throw new CrossRoads_Error("AMI login using MD5 auth Failed!","ASTAUTH");
                return false;
            }
        } else {
            $response = $this->command("Action: login\r\nUsername: ".AMI_USER."\r\nSecret: ".AMI_PASS."\r\n\r\n");
        }

        if(strpos($response, "Message: Authentication accepted") != false) {
            return true;
        } else {
			throw new CrossRoads_Error("AMI login Failed!","ASTAUTH");
			return false;
        }
    }

    /**
    * logout Logout
    */
    public function logout() {
        $this->command("Action: Logoff\r\n\r\n");
    }

    /**
    * disconnect Close socket connection
    */
    public function disconnect() {
        return fclose($this->_socket);
    }

    /**
    * command send an AMI command
    *
    * @param string $command The command to send
    *
    * @return string
    */
    public function command($cmd) {
		$async=false;
        if(is_array($cmd)) {
			$async=($cmd['Async'])?true:false;
            $cmd=$this->buildCommand($cmd);
        }
		if(!fwrite($this->_socket, $cmd)) {
			throw new CrossRoads_Error("Sending Asterisk command Failed!","ASTCMD",'warn');
        }
		if($async) { return true; }
        return stream_get_contents($this->_socket);
    }
    
	/**
    *  build command string from associative array
    *
    *  @param array $arr   action and parameters
    *
    *  @return string      command string
    */
    public function buildCommand($arr) {
        foreach ($arr as $param => $data) {
			if($param=="Variable") {
				$vars=explode("|",$data);
				foreach($vars as $var) { $cmdString.="$param: $var\r\n"; }
			} else {
				$cmdString.="$param: $data\r\n";
			}
        }
        return $cmdString."\r\n";
    }
	
    /**
    * originate a call
    *
    * @param string  $chan      channel we're originating call from ex: SIP/15551231234@ipaddr
    * @param string  $context   context call goes into
    * @param string  $cid       optional caller id
    * @param string  $exten     extension in the context default 's'
    * @param integer $priority  priority of extension in context default 1
    * @param integer $timeout   timeout in milliseconds default 30000
    * @param array   $variables optional array of variables to pass to Asterisk
    * @param string  $aid       optional unique id for originate command
    *
    */
    public function originate($chan,$context,$cid=null,$exten='s',$priority = 1,$timeout = 30000,$variables = null,$aid = null ) {
        $command = array('Action'=>'Originate','Channel'=>$chan,'Context'=>$context,'Exten'=>$exten,'Priority'=>$priority,'Timeout'=>$timeout);
        if($variables != null) {
            foreach ($variables as $key => $val) { $vars[] = "$key=$val"; }
            $vars = implode('|', $vars);
            $command['Variable'] = $vars;
        }
        if($aid != null) {
            $command ['ActionID']= $aid;
        }
        if($callerid != null) {
            $command ['Callerid']=$cid;
        }
        //$command['Async']="true";
        $this->command($command);
        return 1;
    }

    /**
    * get mailbox count
    *
    * @param string mailbox mailbox extension
    * @param string context voicemail-context
    */
    public function  mailboxCount($mailbox,$context) {
        $command = array('Action'=>'MailboxCount','Mailbox'=>"$mailbox@$context");
        return $this->command($command);
    }
}
