<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CMbXPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7v2Message;

$er7   = CValue::post("er7");
$query = CValue::post("query");

$hl7_message = new CHL7v2Message;
$hl7_message->parse($er7);

$xml = $hl7_message->toXML();

if ($query) {
  $xpath = new CMbXPath($xml);
  $results = @$xpath->query("//$query");
  
  $nodes = array();
  
  // Création du template
  $smarty = new CSmartyDP();

  if ($results) {
    foreach ($results as $result) {
      $nodes[] = $xml->saveXML($result);
    }
  }
  
  $smarty->assign("nodes", $nodes);
  $smarty->display("inc_er7_xml_result.tpl");
}
else {
  ob_clean();
  
  header("Content-Type: text/xml");
  echo $xml->saveXML();
  
  CApp::rip();
}
