<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$id_permanent        = CView::get("id_permanent", "num", true);
$exchange_class      = CView::get("exchange_class", "str", true);
$object_id           = CView::get("object_id", "num", true);
$t                   = CView::get('types', "str", true);
$statut_acquittement = CView::get("statut_acquittement", "str", true);
$type                = CView::get("type", "str", true);
$evenement           = CView::get("evenement", "str", true);
$page                = CView::get('page', "num default|0", true);
$group_id            = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id, true);
$_date_min           = CView::get('_date_min', array("dateTime", "default" => CMbDT::dateTime("-7 day")), true);
$_date_max           = CView::get('_date_max', array("dateTime", "default" => CMbDT::dateTime("+1 day")), true);
$actor_guid          = CView::get("actor_guid", "guid class|CInteropActor", true);
$keywords_msg        = CView::get("keywords_msg", "str", true);
$keywords_ack        = CView::get("keywords_ack", "str", true);
$order_col           = CView::get("order_col", "str", true);
$order_way           = CView::get("order_way", "str", true);
$receiver_id         = CView::get("receiver_id", "num", true);
$sender_guid         = CView::get("sender_guid", "guid class|CInteropSender", true);
CView::checkin();
CView::enforceSlave();

/** @var CExchangeDataFormat $exchange */
$exchange = new $exchange_class;

$where = array();
if (isset($t["emetteur"])) {
  $where["sender_id"] = " IS NULL";
}
if (isset($t["destinataire"])) {
  $where["receiver_id"] = " IS NULL";
}
if ($_date_min && $_date_max) {
  $where['date_production'] = " BETWEEN '" . $_date_min . "' AND '" . $_date_max . "' ";
}
if ($group_id) {
  $where["group_id"] = " = '" . $group_id . "'";
}
if ($type) {
  $where["type"] = " = '" . $type . "'";
}
if ($evenement && $exchange instanceof CEchangeXML) {
  $where["sous_type"] = " = '" . $evenement . "'";
}
if ($evenement && $exchange instanceof CExchangeTabular) {
  $where["code"] = " = '" . $evenement . "'";
}

if (isset($t["message_invalide"])) {
  $where["message_valide"] = " = '0'";
}
if (isset($t["acquittement_invalide"])) {
  $where["statut_acquittement"] = " = 'AR'";
}
if (isset($t["no_date_echange"])) {
  $where["send_datetime"] = "IS NULL";
}
if (isset($t["master_idex_missing"])) {
  $where[] = "master_idex_missing = '1'";
}
if ($id_permanent) {
  $where["id_permanent"] = " = '$id_permanent'";
}
if ($object_id) {
  $where["object_id"] = " = '$object_id'";
}
$ljoin = null;
if ($keywords_msg) {
  $content_exchange = $exchange->loadFwdRef("message_content_id");
  $table            = $content_exchange->_spec->table;
  $ljoin[$table]    = $exchange->_spec->table . ".message_content_id = $table.content_id";

  $where["$table.content"] = " LIKE '%$keywords_msg%'";
}

if ($keywords_ack) {
  $content_exchange = $exchange->loadFwdRef("acquittement_content_id");
  $table            = $content_exchange->_spec->table;
  $ljoin[$table]    = $exchange->_spec->table . ".acquittement_content_id = $table.content_id";

  $where["$table.content"] = " LIKE '%$keywords_ack%'";
}

if ($sender_guid) {
  list($sender_class, $sender_id) = explode('-', $sender_guid);

  $where["sender_class"] = " = '$sender_class'";
  $where["sender_id"]    = " = '$sender_id'";
}
if ($receiver_id) {
  $where["receiver_id"] = " = '$receiver_id'";
}

$group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

if ($actor_guid) {
  $actor = CMbObject::loadFromGuid($actor_guid);
  if ($actor instanceof CInteropSender) {
    $where["sender_class"] = " = '$actor->_class'";
    $where["sender_id"]    = " = '$actor->_id'";
  }
  if ($actor instanceof CInteropReceiver) {
    $where["receiver_id"] = " = '$actor->_id'";
  }
}

if (!$actor_guid && $sender_guid && !$receiver_id) {
  $where["group_id"]  = " = '$group_id'";
  $exchange->group_id = $group_id;
}

$exchange->loadRefGroups();

$forceindex[]    = "date_production";
$total_exchanges = $exchange->countList($where, null, $ljoin, $forceindex);
if ($total_exchanges > CAppUI::conf("eai nb_max_export_csv")) {
  CAppUI::displayAjaxMsg("ExchangeDataFormat-action-Export CSV disabled");
  CApp::rip();
}
$order = "$order_col $order_way, {$exchange->_spec->key} DESC";

ob_end_clean();
header("Content-Type: text/plain;charset=" . CApp::$encoding);
header("Content-Disposition: attachment;filename=\"export_stream_hl7.csv\"");

$fp  = fopen("php://output", "w");
$csv = new CCSVFile($fp);

$titles = array(
  CAppUI::tr("CInteropReceiver-libelle"),
  CAppUI::tr("Message-id"),
  CAppUI::tr("Movement-id"),
  CAppUI::tr("Message-type"),
  CAppUI::tr("Action"),
  CAppUI::tr("Initial-movement"),
  CAppUI::tr("Date-of-movement"),
  CAppUI::tr("CPatient-_IPP"),
  CAppUI::tr("NDA"),
  CAppUI::tr("UF-H"),
  CAppUI::tr("UF-M"),
  CAppUI::tr("UF-S"),
  CAppUI::tr("CChambre"),
  CAppUI::tr("CLit"),
  CAppUI::tr("CSejour-praticien_id-desc"),
  CAppUI::tr("CSejour-entree_prevue"),
  CAppUI::tr("CSejour-sortie_prevue"),
  CAppUI::tr("CSejour-entree_reelle"),
  CAppUI::tr("CSejour-sortie_reelle"),
);
$csv->writeLine($titles);

/** @var CExchangeHL7v2 [] $exchanges */
$exchanges = $exchange->loadList($where, $order, null, null, $ljoin, $forceindex);
foreach ($exchanges as $_exchange) {
  $_exchange->loadRefsInteropActor();
  if ($_exchange->receiver_id) {
    /** @var CInteropReceiver $actor */
    $actor = $_exchange->_ref_receiver;
    $actor->loadConfigValues();
  }
  else {
    /** @var CInteropSender $actor */
    $actor = $_exchange->_ref_sender;
    $actor->getConfigs($_exchange);
  }

  $hl7Message = $_exchange->getMessage();

  /** @var CHL7v2MessageXML $xml */
  $xml = $hl7Message->toXML(null, false);

  $MSH = $xml->queryNode("//MSH");
  $PID = $xml->queryNodeByIndex("//PID");
  $PV1 = $xml->queryNodeByIndex("//PV1");
  $PV2 = $xml->queryNodeByIndex("//PV2");
  $ZBE = $xml->queryNode("//ZBE");

  $data = array(
    "ACTOR_LIBELLE" => null, "ID_MSG" => null, "ID_MOVEMENT" => null, "TYPE_MSG" => null, "ACTION" => null, "MI" => null, "DATE_MOVEMENT" => null, "IPP" => null, "NDA" => null,
    "UF-H"          => null, "UF-M" => null, "UF-S" => null, "CHAMBRE" => null, "LIT" => null, "PRAT_RESP" => null, "ENTREE_PREVUE" => null,
    "SORTIE_PREVUE" => null, "ENTREE_REELLE" => null, "SORTIE_REELLE" => null,
  );

  $data["ACTOR_LIBELLE"] = $actor->libelle;
  $name_config           = $_exchange->receiver_id ? "build_NDA" : "handle_NDA"; // ? receiver : sender
  // NDA
  if (CMbArray::get($actor->_configs, "$name_config") === "PID_18") {
    $data["NDA"] = $PID ? escapeData($xml->queryTextNode("PID.18/CX.1", $PID)) : null;
  }
  else {
    $data["NDA"] = $PV1 ? escapeData($xml->queryTextNode("PV1.19/CX.1", $PV1)) : null;
  }

  //IPP
  foreach ($xml->query("PID.3", $PID) as $_node) {
    $identifier_type_code = $xml->queryTextNode("CX.5", $_node);
    if ($identifier_type_code === "PI") {
      $data["IPP"] = escapeData($xml->queryTextNode("CX.1", $_node));
    }
  }

  $data["DATE_MOVEMENT"] = CMbDT::dateToLocale($xml->queryTextNode("PID.33", $PID));
  $data["TYPE_MSG"]      = $xml->queryTextNode("MSH.9/MSG.2", $MSH);
  $data["ID_MSG"]        = escapeData($xml->queryTextNode("MSH.10", $MSH));

  if ($ZBE) {
    $data["ID_MOVEMENT"] = escapeData($xml->queryTextNode("ZBE.1/EI.1", $ZBE));
    $data["ACTION"]      = $xml->queryTextNode("ZBE.4", $ZBE);
    $data["MI"]          = $xml->queryTextNode("ZBE.6", $ZBE);
    if ($ZBE_7 = $xml->queryNode("ZBE.7", $ZBE)) {
      $data["UF-M"] = escapeData($xml->queryTextNode("XON.10", $ZBE_7));
    }
    if ($ZBE_8 = $xml->queryNode("ZBE.8", $ZBE)) {
      $data["UF-S"] = escapeData($xml->queryTextNode("XON.10", $ZBE_8));
    }
  }

  if ($PV1) {
    if ($PV1_3 = $xml->queryNode("PV1.3", $PV1)) {
      $data["LIT"]     = escapeData($xml->queryTextNode("PL.3", $PV1_3));
      $data["CHAMBRE"] = escapeData($xml->queryTextNode("PL.2", $PV1_3));
      $data["UF-H"]    = escapeData($xml->queryTextNode("PL.1", $PV1_3));
    }
    if ($PV1_7 = $xml->query("PV1.7", $PV1)->item(0)) {
      $firstname         = $xml->queryTextNode("XCN.2/FN.1", $PV1_7);
      $data["PRAT_RESP"] = $firstname . " " . $xml->queryTextNode("XCN.3", $PV1_7);
    }
    $data["ENTREE_REELLE"] = CMbDT::dateToLocale($xml->queryTextNode("PV1.44/TS.1", $PV1));
    $data["SORTIE_REELLE"] = CMbDT::dateToLocale($xml->queryTextNode("PV1.45/TS.1", $PV1));
  }

  if ($PV2) {
    $data["ENTREE_PREVUE"] = CMbDT::dateToLocale($xml->queryTextNode("PV2.8/TS.1", $PV2));
    $data["SORTIE_PREVUE"] = CMbDT::dateToLocale($xml->queryTextNode("PV2.9/TS.1", $PV2));
  }
  $csv->writeLine($data);
}
CApp::rip();

/**
 * @param string $data
 *
 * @return string|null
 */
function escapeData($data) {
  if (!is_string($data) || $data === "") {
    return null;
  }

  return "'" . $data . "'";
}

