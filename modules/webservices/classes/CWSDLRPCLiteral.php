<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;

/**
 * Class CWSDLRPCLiteral 
 * Format RPC Literal
 */
class CWSDLRPCLiteral extends CWSDLRPC {
  /**
   * Add types
   *
   * @return void
   */
  function addTypes() {
    $definitions = $this->documentElement;
    $partie2 = $this->createComment("partie 2 : Types");
    $definitions->appendChild($partie2);
    $types = $this->addElement($definitions, "types", null, "http://schemas.xmlsoap.org/wsdl/");
    
    $xsd = $this->addElement($types, "xsd:schema", null, "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($xsd, "elementFormDefault", "qualified");
    $this->addAttribute($xsd, "xmlns", "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($xsd, "targetNamespace", "http://soap.mediboard.org/wsdl/");
    
    // Foreach method to describe
    foreach ($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      // MethodRequest element
      // Foreach parameters
      foreach ($_paramSpec["parameters"] as $_param => $_type) {
        $child_element = $this->addElement($xsd, "element", null, "http://www.w3.org/2001/XMLSchema");
        $this->addAttribute($child_element, "name", $_method."-".$_param);
        
        $this->addDocumentation($child_element, CAppUI::tr(CClassMap::getSN($this->_soap_handler)."-".$_method."-".$_param));
        
        if (is_array($_type)) {
          $this->addComplexType($_type, $child_element, $_method."-".$_param, $xsd);
        }
        else {
          $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
        }
      }

      // MethodResponse element
      // Foreach returns
      foreach ($_paramSpec["return"] as $_return => $_type) {
        $child_element = $this->addElement($xsd, "element", null, "http://www.w3.org/2001/XMLSchema");
        $this->addAttribute($child_element, "name", $_method."-".$_return);

        $this->addDocumentation($child_element, CAppUI::tr(CClassMap::getSN($this->_soap_handler)."-".$_method."-".$_return));

        if (is_array($_type)) {
          $this->addComplexType($_type, $child_element, $_method."-".$_return, $xsd);
        }
        else {
          $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
        }
      }
    }
    
    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * Add message
   *
   * @return void
   */
  function addMessage() {
    $definitions = $this->documentElement;
    $partie3 = $this->createComment("partie 3 : Message");
    $definitions->appendChild($partie3);
    
    foreach ($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $message = $this->addElement($definitions, "message", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($message, "name", $_method."Request");
      
      foreach ($_paramSpec['parameters'] as $_oneParam => $_paramType) {
        $part = $this->addElement($message, "part", null, "http://schemas.xmlsoap.org/wsdl/");
        $this->addAttribute($part, "name", $_oneParam);
        $this->addAttribute($part, "element", "typens:".$_method."-".$_oneParam);
      }
      
      $message = $this->addElement($definitions, "message", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($message, "name", $_method."Response");
      
      foreach ($_paramSpec['return'] as $_oneParam => $_paramType) {
        $part = $this->addElement($message, "part", null, "http://schemas.xmlsoap.org/wsdl/");
        $this->addAttribute($part, "name", $_oneParam);
        $this->addAttribute($part, "element", "typens:".$_method."-".$_oneParam);
      }
      
      // SOAP Fault
      /*$message = $this->addElement($definitions, "message", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($message, "name", $_method."Fault");*/
    }
  }

  /**
   * Add port type
   *
   * @return void
   */
  function addPortType() {
    $definitions = $this->documentElement;
    $partie4 = $this->createComment("partie 4 : Port Type");
    $definitions->appendChild($partie4);
    
    $portType = $this->addElement($definitions, "portType", null, "http://schemas.xmlsoap.org/wsdl/");
    $this->addAttribute($portType, "name", "MediboardPort");
    
    foreach ($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $partie5 = $this->createComment("partie 5 : Operation");
      $portType->appendChild($partie5);
      $operation = $this->addElement($portType, "operation", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($operation, "name", $_method);
      
      $input = $this->addElement($operation, "input", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($input, "message", "typens:".$_method."Request");
      
      $output = $this->addElement($operation, "output", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($output, "message", "typens:".$_method."Response");
      
      // SOAP Fault
      /*$fault = $this->addElement($operation, "fault", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($fault, "message", "typens:".$_method."Fault");*/
    }
  }

  /**
   * Add binding
   *
   * @return void
   */
  function addBinding() {
    $definitions = $this->documentElement;
    $partie6 = $this->createComment("partie 6 : Binding");
    $definitions->appendChild($partie6);
    
    $binding = $this->addElement($definitions, "binding", null, "http://schemas.xmlsoap.org/wsdl/");
    $this->addAttribute($binding, "name", "MediboardBinding");
    $this->addAttribute($binding, "type", "typens:MediboardPort");
    
    $soap = $this->addElement($binding, "soap:binding", null, "http://schemas.xmlsoap.org/wsdl/soap/");
    $this->addAttribute($soap, "style", "rpc");
    $this->addAttribute($soap, "transport", "http://schemas.xmlsoap.org/soap/http");

    foreach ($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $operation = $this->addElement($binding, "operation", null, "http://schemas.xmlsoap.org/wsdl/");
      
      $this->addAttribute($operation, "name", $_method);
      
      $soapoperation = $this->addElement($operation, "soap:operation", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapoperation, "soapAction", "");
      
      $input = $this->addElement($operation, "input", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($input, "name", $_method."Request");
      
      $soapbody = $this->addElement($input, "soap:body", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapbody, "use", "literal");
      
      $output = $this->addElement($operation, "output", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($output, "name", $_method."Response");
      
      $soapbody = $this->addElement($output, "soap:body", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapbody, "use", "literal");
      
      // SOAP Fault
      /*$fault = $this->addElement($operation, "fault", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($fault, "name", $_method."Exception");
      
      $soapfault = $this->addElement($fault, "soap:fault", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapfault, "name", $_method."Exception");
      $this->addAttribute($soapfault, "use", "literal");*/
    }
  }

  /**
   * Add complexType
   *
   * @return void
   */
  function addComplexType($type, $child_element, $name, $xsd) {
    $complexType = $this->addElement($child_element, "complexType", null, "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($complexType, "name", $name);

    $sequence = $this->addElement($complexType, "sequence", null, "http://www.w3.org/2001/XMLSchema");
    // Foreach array parameters
    foreach ($type as $_paramName => $_arrayType) {
      if (is_array($_arrayType)) {
        $element_array = $this->addElement($sequence, "xsd:element", null, "http://soap.mediboard.org/wsdl/");
        $this->addAttribute($element_array, "name", $name."-elements");
        $this->addAttribute($element_array, "type", "typens:$name-element-$_paramName");
        $this->addAttribute($element_array, "maxOccurs", "unbounded");

        $this->addComplexType($_arrayType, $xsd, "$name-element-$_paramName", $xsd);
      }
      else {
        $child_element = $this->addElement($sequence, "element", null, "http://www.w3.org/2001/XMLSchema");
        $this->addAttribute($child_element, "name", $_paramName);
        $this->addAttribute($child_element, "type", "xsd:" . $this->xsd[$_arrayType]);

        $this->addDocumentation($child_element, CAppUI::tr(CClassMap::getSN($this->_soap_handler)."-$name-$_paramName"));
      }
    }
  }
}
