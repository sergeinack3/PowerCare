<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Hl7\CExchangeHL7v2;

/**
 * Download exchange XML
 */
$exchange_guid = CValue::get("exchange_guid");
$exchange_object = CMbObject::loadFromGuid($exchange_guid);
$exchange_object->loadRefs();

$extension = ".txt";
if ($exchange_object instanceof CEchangeXML) {
  $extension = ".xml";    
}
if ($exchange_object instanceof CExchangeHL7v2) {
  $extension = ".HL7";    
}

if (CValue::get("message") == 1) {
  $exchange = utf8_decode($exchange_object->_message);
  
  $filename = "msg-{$exchange_object->sous_type}-{$exchange_object->_id}{$extension}";
  header("Content-Disposition: attachment; filename=$filename");
  header("Content-Type: text/plain; charset=".CApp::$encoding);
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
  header("Cache-Control: post-check=0, pre-check=0", false );
  header("Content-Length: ".strlen($exchange));
  echo $exchange;
}
if (CValue::get("ack") == 1) {
  $exchange = utf8_decode($exchange_object->_acquittement);
  
  $filename = "ack-{$exchange_object->sous_type}-{$exchange_object->_id}{$extension}";
  header("Content-Disposition: attachment; filename=$filename");
  header("Content-Type: text/plain; charset=".CApp::$encoding);
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
  header("Cache-Control: post-check=0, pre-check=0", false );
  header("Content-Length: ".strlen($exchange));
  echo $exchange;
}

