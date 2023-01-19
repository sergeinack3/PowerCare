<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use DOMNode;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;

/**
 * Class CHPrim21MessageXML 
 * Message XML HPR
 */
class CHPrim21MessageXML extends CMbXMLDocument {
  /** @var  CEchangeHprim21 */
  public $_ref_exchange_hpr;
  public $_ref_sender;
  public $_ref_receiver;
  
  static function getEventType($event_name = null, $encoding = "utf-8") {
    if (!$event_name) {
      return new CHPrim21MessageXML($encoding);
    }
        
    // Transfert de données d'admission
    if (strpos($event_name, "CHPrim21ADM") === 0) {
      // Do nothing
    }
    
    // Transfert de données de règlement
    if (strpos($event_name, "CHPrim21REG") === 0) {
      return new CHprim21RecordPayment($encoding);
    }

    // Transfert de données de règlement
    if (strpos($event_name, "CHPrim21ORU") === 0) {
      return new CHprim21RecordFiles($encoding);
    }
    
    return new CHPrim21MessageXML($encoding);
  }
  
  function __construct($encoding = "utf-8") {
    parent::__construct($encoding);

    $this->formatOutput = true;
  }
  
  function addNameSpaces($name) {
    // Ajout des namespace pour XML Spy
    $this->addAttribute($this->documentElement, "xmlns", "urn:hpr-org:v2xml");
    $this->addAttribute($this->documentElement, "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
    $this->addAttribute($this->documentElement, "xsi:schemaLocation", "urn:hpr-org:v2xml");
  }
  
  function addElement(DOMNode $elParent, $elName, $elValue = null, $elNS = "urn:hpr-org:v2xml") {
    return parent::addElement($elParent, $elName, $elValue, $elNS);
  }
  
  function query($nodeName, DOMNode $contextNode = null) {
    $xpath = new CHPrim21MessageXPath($contextNode ? $contextNode->ownerDocument : $this);   
    
    if ($contextNode) {
      return $xpath->query($nodeName, $contextNode);
    }
    
    return $xpath->query($nodeName);
  }
  
  function queryNode($nodeName, DOMNode $contextNode = null, &$data = null, $root = false) {
    $xpath = new CHPrim21MessageXPath($contextNode ? $contextNode->ownerDocument : $this);   
        
    return $data[$nodeName] = $xpath->queryUniqueNode($root ? "//$nodeName" : "$nodeName", $contextNode);
  }
  
  function queryNodes($nodeName, DOMNode $contextNode = null, &$data = null, $root = false) {
    $nodeList = $this->query("$nodeName", $contextNode);
    foreach ($nodeList as $_node) {
      $data[$nodeName][] = $_node;
    }
    
    return $nodeList;
  }
  
  function queryTextNode($nodeName, DOMNode $contextNode, $root = false) {
    $xpath = new CHPrim21MessageXPath($contextNode ? $contextNode->ownerDocument : $this);   
    
    return $xpath->queryTextNode($nodeName, $contextNode);
  }
  
  function getSegment($name, $data, $object) {
    if (!array_key_exists($name, $data) || $data[$name] === null) {
      return;
    }
    
    $function = "get$name";
    
    $this->$function($data[$name], $object);
  }
  
  function getHEvenementXML() {
    $data = array();
    
    $H = $this->queryNode("H", null, $foo, true);
    
    $data['dateHeureProduction'] = CMbDT::dateTime($this->queryTextNode("H.13/TS.1", $H));
    $data['filename']            = $this->queryTextNode("H.2", $H);
    
    return $data;
  }

  /**
   * @return array
   */
  function getContentNodes() {
    return array();
  }
  
  function handle($ack, CMbObject $newPatient, $data) {
  }
}
