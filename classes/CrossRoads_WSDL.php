<?php
/**
 * CrossRoads API: WebService
 *
 * This is the CrossRoads API WebService class.
 * Its purpose is to generate WSDL web services.
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
 * class CrossRoads_WSDL
 *
 * This is the WSDL builder class.
 * It currently only builds wsdls for simple web services
 * This thing will need to be cleaned/extended for more complex services
 *
 * @category WebServices
 * @package  CrossRoads
 * @author   Michael Myles <michael.myles@thing5.com>
 * @author   Alex Nikitin <anikitin@juniper.net>
 * @license  GPL-2.0    http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://atomicmaster.com
 */
 
class CrossRoads_WSDL extends DomDocument
{
	private $_obj,$_methInfo;
	function __construct($object) {
		header ("content-type: text/xml");

		$this->_obj=$object;
		$this->_methInfo=$this->getMethodInfo();

		parent::__construct('1.0');

		$this->formatOutput = true;
		$this->encoding     = "UTF-8";

		$definitions = $this->createElement('definitions');
		$definitions->setAttribute('name', MOD_CLASS);
		$definitions->setAttribute('targetNamespace', TARGET_NAMESPACE);
		$definitions->setAttribute('xmlns:tns', TARGET_NAMESPACE);
		$definitions->setAttribute('xmlns:soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
		$definitions->setAttribute('xmlns:xsd','http://www.w3.org/2001/XMLSchema');
		$definitions->setAttribute('xmlns:soapenc','http://schemas.xmlsoap.org/soap/encoding/');
		$definitions->setAttribute('xmlns:wsdl','http://schemas.xmlsoap.org/wsdl/');
		$definitions->setAttribute('xmlns', 'http://schemas.xmlsoap.org/wsdl/');

		$this->appendChild($definitions);
		$this->buildMessageStanzas($definitions);
		$this->buildPortTypes($definitions);
		$this->buildBindings($definitions);
		$this->buildService($definitions);

		header ("content-type: text/xml");
		echo $this->saveXML();
	}

    private function getMethodInfo() {
        $methods=array();
        foreach($this->_obj->ownMethods() as $method) {
            $meth = CrossRoads_Reflection::reflect(MOD_CLASS."::{$method}");
            if($docBlock = CrossRoads_DocBlocker::getInfo($meth)) {
                $methods[]=$docBlock;
            }
        }
        return $methods;
    }

    private function buildMessageStanzas($node) {

        foreach($this->_methInfo as $method) {
            $messageRequest=$this->createElement('message');
            $messageRequest->setAttribute('name',$method['method']."Request");
            $messageResponse=$this->createElement('message');
            $messageResponse->setAttribute('name',$method['method']."Response");
            foreach($method['@param'] as $params) {
                 $part=$this->createElement("part");
                 $part->setAttribute('name',$params[2]);
                 switch($params[1]) {
					case "mixed":
                        $part->setAttribute('type',"xsd:string");
                        break;
                    default:
                        $part->setAttribute('type',"xsd:".$params[1]);
                 }
                 $messageRequest->appendChild($part);
            }
            foreach($method['@return'] as $returns) {
                 $part=$this->createElement("part");
                 $part->setAttribute('name','result');
                 switch($returns[1]) {
                    case "mixed":
                        $part->setAttribute('type',"xsd:string");
                        break;
                    default:
                        $part->setAttribute('type',"xsd:".$returns[1]);
                 }
                 $messageResponse->appendChild($part);
            }
            $node->appendChild($messageRequest);
            $node->appendChild($messageResponse);
        }
    }

    private function buildPortTypes($node) {
            $portType=$this->createElement("portType");
            $portType->setAttribute("name",MOD_CLASS."PortType");
            foreach($this->_methInfo as $method) {
                $operation=$this->createElement('operation');
                $operation->setAttribute('name',$method['method']);

                $input=$this->createElement('input');
                $input->setAttribute('message','tns:'.$method['method']."Request");

                $output=$this->createElement('output');
                $output->setAttribute('message','tns:'.$method['method']."Response");

                $operation->appendChild($input);
                $operation->appendChild($output);
                $portType->appendChild($operation);
            }
            $node->appendChild($portType);
    }

    private function buildBindings($node) {
        $binding=$this->createElement('binding');
        $binding->setAttribute('name',MOD_CLASS."Binding");
        $binding->setAttribute('type','tns:'.MOD_CLASS."PortType");

        $soapBinding=$this->createElement("soap:binding");
        $soapBinding->setAttribute('style','rpc');
        $soapBinding->setAttribute('transport','http://schemas.xmlsoap.org/soap/http');

        $binding->appendChild($soapBinding);

        foreach ($this->_methInfo as $methods) {
            $operation=$this->createElement("operation");
            $operation->setAttribute('name',$methods['method']);

            $doc=$this->createElement("documentation");
            $doc->appendChild($this->createTextNode($methods['description']));

            $soapOperation=$this->createElement("soap:operation");
            $soapOperation->setAttribute("soapAction","urn:".strtolower(TARGET_NAMESPACE)."#".$methods['method']);

            $input=$this->createElement("input");
            $inSoapBody=$this->createElement("soap:body");
            $inSoapBody->setAttribute("use","encoded");
            $inSoapBody->setAttribute("namespace","urn:".strtolower(TARGET_NAMESPACE));
            $inSoapBody->setAttribute("encodingStyle",'http://schemas.xmlsoap.org/soap/encoding/');

            $output=$this->createElement("output");
            $outSoapBody=$this->createElement("soap:body");
            $outSoapBody->setAttribute("use","encoded");
            $outSoapBody->setAttribute("namespace","urn:".strtolower(TARGET_NAMESPACE));
            $outSoapBody->setAttribute("encodingStyle",'http://schemas.xmlsoap.org/soap/encoding/');

            $input->appendChild($inSoapBody);
            $output->appendChild($outSoapBody);


            if(trim($methods['description'])!=="") {
                $operation->appendChild($doc);
            }

            $operation->appendChild($soapOperation);
            $operation->appendChild($input);
            $operation->appendChild($output);
            $binding->appendChild($operation);
            $node->appendChild($binding);
        }
    }

    private function buildService($node) {
		$service=$this->createElement("service");
		$service->setAttribute("name",MOD_CLASS."Service");

		$port=$this->createElement("port");
		$port->setAttribute("name",MOD_CLASS."Port");
		$port->setAttribute("binding",MOD_CLASS."Binding");

		$soapAddress=$this->createElement("soap:address");
		$soapAddress->setAttribute("location","https://".$_SERVER['HTTP_HOST'].DS.MOD_NAME.DS."soap");

		$port->appendChild($soapAddress);
		$service->appendChild($port);
		$node->appendChild($service);
    }
}
