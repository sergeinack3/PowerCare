<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Interop\Webservices\CEchangeSOAP;

/**
 * View exchanges
 */
CCanDo::checkRead();

$echange_soap_id = CValue::get("echange_soap_id");
$page            = CValue::get('page', 0);
$now             = CMbDT::date();
$_date_min       = CValue::getOrSession('_date_min', CMbDT::dateTime("-7 day"));
$_date_max       = CValue::getOrSession('_date_max', CMbDT::dateTime("+1 day"));
$service         = CValue::getOrSession("service");
$web_service     = CValue::getOrSession("web_service");
$fonction        = CValue::getOrSession("fonction");

CValue::setSession("web_service", $web_service);
CValue::setSession("service"    , $service);
CValue::setSession("_date_min"  , $_date_min);
CValue::setSession("_date_max"  , $_date_max);
CValue::setSession("fonction"   , $fonction);

$doc_errors_msg = $doc_errors_ack = "";

// Chargement de l'échange SOAP demandé
$echange_soap = new CEchangeSOAP();

$echange_soap->load($echange_soap_id);
if ($echange_soap->_id) {
  $echange_soap->loadRefs();

  $echange_soap->input  = unserialize($echange_soap->input);
  if ($echange_soap->soapfault != 1) {
    $echange_soap->output = unserialize($echange_soap->output);
  }
}

// Récupération de la liste des echanges SOAP
$itemEchangeSoap = new CEchangeSOAP;

$where = array();
if ($_date_min && $_date_max) {
  $echange_soap->_date_min = $_date_min;
  $echange_soap->_date_max = $_date_max;
  $where['date_echange'] = " BETWEEN '".$_date_min."' AND '".$_date_max."' ";
}
if ($service) {
  $where['type'] = " = '".$service."'";
}
if ($fonction) {
  $where['function_name'] = " = '".$fonction."'";
}
if ($web_service) {
  $where["web_service_name"] = " = '".$web_service."'";
}

$total_echange_soap = 0;
$echangesSoap = array();
if (($service && $web_service) || ($service && $_date_min && $_date_max)) {
  $total_echange_soap = $itemEchangeSoap->countList($where);
  $order = "date_echange DESC";
  $forceindex[] = "date_echange";
  $echangesSoap = $itemEchangeSoap->loadList($where, $order, "$page, 20", null, null, $forceindex);
}


foreach ($echangesSoap as $_echange_soap) {
  /** @var $_echange_soap CEchangeSOAP  */
  $destinataire = $_echange_soap->destinataire;
  $url = parse_url($destinataire);
  if (!CMbArray::get($url, "host")) {
    $name = basename($destinataire);
    $url['host'] = $name;
  }
  $_echange_soap->destinataire = $url['host'];
}

$services = array();

if (!$echange_soap->_id) {
  $ds = CSQLDataSource::get("std");
  $services = $ds->loadColumn("SELECT type FROM echange_soap GROUP BY type");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("echange_soap"       , $echange_soap);
$smarty->assign("echangesSoap"       , $echangesSoap);
$smarty->assign("total_echange_soap" , $total_echange_soap);
$smarty->assign("page"               , $page);

$smarty->assign("service"            , $service);
$smarty->assign("web_service"        , $web_service);
$smarty->assign("fonction"           , $fonction);
$smarty->assign("services"           , $services);

$smarty->display("inc_search_echange_soap.tpl");