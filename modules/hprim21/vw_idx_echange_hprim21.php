<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hprim21\CEchangeHprim21;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Vue des échanges Hprim21
 */
CCanDo::checkRead();

$echange_hprim21_id = CValue::get("echange_hprim21_id");
$t                  = CValue::getOrSession('types', array());
$type_message       = CValue::getOrSession("type_message");
$page               = CValue::get('page', 0);
$_date_min          = CValue::getOrSession('_date_min', CMbDT::dateTime("-7 day"));
$_date_max          = CValue::getOrSession('_date_max', CMbDT::dateTime("+1 day"));

// Types filtres qu'on peut prendre en compte
$filtre_types = array('no_date_echange', 'message_invalide');

$types = array();
foreach ($filtre_types as $type) {
  $types[$type] = !isset($t) || in_array($type, $t);
}
 
// Chargement de l'échange HPRIM 2.1 demandé
$echg_hprim21 = new CEchangeHprim21();
$echg_hprim21->_date_min = $_date_min;
$echg_hprim21->_date_max = $_date_max;

$echg_hprim21->load($echange_hprim21_id);
$echg_hprim21->loadRefsInteropActor();

// Récupération de la liste des echanges HPRIM 2.1
$itemEchangeHprim21 = new CEchangeHprim21();

$where = array();

if ($_date_min && $_date_max) {
  $where['date_production'] = " BETWEEN '".$_date_min."' AND '".$_date_max."' "; 
}
if ($type_message) {
  $where["type_message"] = " = '$type_message'";
}
if (isset($t["message_invalide"])) {
  $where["message_valide"] = " = '0'";
}
if (isset($t["no_date_echange"])) {
  $where["send_datetime"] = "IS NULL";
}

$where["group_id"] = "= '".CGroups::loadCurrent()->_id."'";

$total_echange_hprim21 = $itemEchangeHprim21->countList($where);
$order = "date_production DESC";
$forceindex[] = "date_production";

/** @var CEchangeHprim21[] $echangesHprim21 */
$echangesHprim21 = $itemEchangeHprim21->loadList($where, $order, "$page, 20", null, null, $forceindex);
foreach ($echangesHprim21 as $_echange) {
  $_echange->loadRefsInteropActor();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("echg_hprim21"         , $echg_hprim21);
$smarty->assign("echangesHprim21"      , $echangesHprim21);
$smarty->assign("total_echange_hprim21", $total_echange_hprim21);
$smarty->assign("page"                 , $page);
$smarty->assign("selected_types"       , $t);
$smarty->assign("types"                , $types);
$smarty->assign("type_message"         , $type_message);

$smarty->display("vw_idx_echange_hprim21.tpl");


