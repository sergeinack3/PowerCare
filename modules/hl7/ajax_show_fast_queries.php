<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;

$er7         = CValue::post("er7");
$exchange_id = CValue::post("exchange_id");

$exchange = new CExchangeHL7v2();
$exchange->load($exchange_id);
$exchange->loadRefsInteropActor();

if ($exchange->receiver_id) {
  /** @var CInteropReceiver $actor */
  $actor = $exchange->_ref_receiver;
  $actor->loadConfigValues();
}
else {
  /** @var CInteropSender $actor */
  $actor = $exchange->_ref_sender;
  $actor->getConfigs($exchange);
}

$hl7_message = new CHL7v2Message;
$hl7_message->parse($er7);

/** @var CHL7v2MessageXML $xml */
$xml = $hl7_message->toXML(null ,false);

$MSH = $xml->queryNode("//MSH");
$EVN = $xml->queryNode("//EVN");
$PID = $xml->queryNodeByIndex("//PID");
$PV1 = $xml->queryNodeByIndex("//PV1");
$PV2 = $xml->queryNodeByIndex("//PV2");
$ZBE = $xml->queryNode("//ZBE");

$IPP = $NDA = null;

if ($handle_mode = CMbArray::get($actor->_configs, "handle_mode")) {
  CHL7v2Message::setHandleMode($handle_mode);
}

$data = array();
$data["personIdentifiers"] = $xml->getPersonIdentifiers("PID.3", $PID, $actor);
$data["admitIdentifiers"]  = $PV1 ? $xml->getAdmitIdentifiers($PV1, $actor) : array();

$names = array(
  "nom"             => "",
  "nom_jeune_fille" => ""
);

$prenom = null;
$PID5 = $xml->query("PID.5", $PID);
foreach ($PID5 as $_PID5) {
  // Nom(s)
  getNames($xml, $_PID5, $PID5, $names);
  
  // Prenom(s)
  $prenom = getFirstNames($xml, $_PID5);
}      

$queries = array(
  "Message" => array(
    "control_id" => $xml->queryTextNode("MSH.10", $MSH),
    "datetime"   => CMbDT::dateToLocale($xml->queryTextNode("MSH.7/TS.1", $MSH)),
  ),
  "EVN" => array(
    "planned_event"  => CMbDT::dateToLocale($xml->queryTextNode("EVN.2/TS.1", $EVN)),
    "event_occurred" => CMbDT::dateToLocale($xml->queryTextNode("EVN.6/TS.1", $EVN)),
  ),
  "CPatient" => array(
    "nom"             => $names["nom"],
    "nom_jeune_fille" => $names["nom_jeune_fille"],
    "prenom"          => $prenom,
    "naissance"       => CMbDT::dateToLocale($xml->queryTextNode("PID.7", $PID)),
    "_IPP"            => CValue::read($data["personIdentifiers"], "PI"),
  ),
  "CSejour" => array(
    "type"          => $xml->queryTextNode("PV1.2", $PV1),
    "entree_prevue" => CMbDT::dateToLocale($xml->queryTextNode("PV2.8/TS.1", $PV2)),
    "entree_reelle" => CMbDT::dateToLocale($xml->queryTextNode("PV1.44/TS.1", $PV1)),
    "sortie_prevue" => CMbDT::dateToLocale($xml->queryTextNode("PV2.9/TS.1", $PV2)),
    "sortie_reelle" => CMbDT::dateToLocale($xml->queryTextNode("PV1.45/TS.1", $PV1)),
    "_NDA"          => CValue::read($data["personIdentifiers"], "AN")
  )
);

if ($ZBE) {
  $movement_id = null;
  foreach ($xml->queryNodes("ZBE.1", $ZBE) as $ZBE_1) {
    $movement_id .= $xml->queryTextNode("EI.1", $ZBE_1) . "\n";
  }
  $queries_ZBE = array(
    "CMovement" => array(
      "movement_id"       => $movement_id,
      "start_of_movement" => CMbDT::dateToLocale($xml->queryTextNode("ZBE.2/TS.1", $ZBE)),
    )
  );

  $queries = array_merge($queries, $queries_ZBE);
}

function getNames(CHL7v2MessageXML $xml, DOMNode $node, DOMNodeList $PID5, &$names = array()) {
  $fn1 = $xml->queryTextNode("XPN.1/FN.1", $node);
  
  switch ($xml->queryTextNode("XPN.7", $node)) {
    case "D":
      $names["nom"] = $fn1;
      break;
    case "L":
      // Dans le cas où l'on a pas de nom de nom de naissance le legal name
      // est le nom du patient
      if ($PID5->length > 1) {
        $names["nom_jeune_fille"] = $fn1;
      }
      else {
        $names["nom"] = $fn1;
      }
      break;
    default:
  }  
}

function getFirstNames(CHL7v2MessageXML $xml, DOMNode $node) {
  return $xml->queryTextNode("XPN.2", $node);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("queries", $queries);
$smarty->display("inc_show_fast_queries.tpl");
