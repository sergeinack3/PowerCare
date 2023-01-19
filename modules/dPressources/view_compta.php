<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ressources\CPlageressource;

CCanDo::checkEdit();

$ds = CSQLDataSource::get("std");

//Recuperation des identifiants pour les filtres
$filter            = new CPlageressource;
$filter->_date_min = CValue::getOrSession("_date_min", CMbDT::date());
$filter->_date_max = CValue::getOrSession("_date_max", CMbDT::date());
$filter->prat_id   = CValue::getOrSession("prat_id");
$filter->paye      = CValue::getOrSession("paye");
// Chargement de la liste des praticiens pour l'historique

$listPrats = new CMediusers;

$where                   = array();
$where[]                 = "plageressource.date < '" . CMbDT::date() . "'";
$where[]                 = "plageressource.prat_id IS NOT NULL";
$where[]                 = "plageressource.prat_id <> 0";
$ljoin                   = array();
$ljoin["plageressource"] = "plageressource.prat_id = users_mediboard.user_id";
$ljoin["users"]          = "users.user_id = users_mediboard.user_id";
$group                   = "prat_id";
$order                   = "users.user_last_name";

$listPrats = $listPrats->loadList($where, $order, null, $group, $ljoin);
foreach ($listPrats as &$prat) {
  $prat->loadRefFunction();
}

// Chargement de la liste des impayés
$sql      = "SELECT prat_id" .
  "\nFROM plageressource" .
  "\nWHERE date < '" . CMbDT::date() . "'" .
  "\nAND prat_id IS NOT NULL" .
  "\nAND prat_id <> 0" .
  "\nGROUP BY prat_id" .
  "\nORDER BY prat_id";
$sqlPrats = $ds->loadlist($sql);

$total          = array();
$total["total"] = 0;
$total["somme"] = 0;
$total["prat"]  = 0;

$sql  = "SELECT prat_id," .
  "\nCOUNT(plageressource_id) AS total," .
  "\nSUM(tarif) AS somme" .
  "\nFROM plageressource" .
  "\nWHERE date < '" . CMbDT::date() . "'" .
  "\nAND prat_id IS NOT NULL" .
  "\nAND prat_id <> 0" .
  "\nAND paye = '0'" .
  "\nGROUP BY prat_id" .
  "\nORDER BY somme DESC";
$list = $ds->loadlist($sql);

$where         = array();
$where["date"] = "< '" . CMbDT::date() . "'";
$where["paye"] = "= '0'";
$order         = "date";
foreach ($list as $key => $value) {
  $total["total"] += $value["total"];
  $total["somme"] += $value["somme"];
  $total["prat"]++;
  $where["prat_id"]        = "= '" . $value["prat_id"] . "'";
  $list[$key]["praticien"] = new CMediusers;
  $list[$key]["praticien"]->load($value["prat_id"]);
  $list[$key]["plages"] = new CPlageressource;
  $list[$key]["plages"] = $list[$key]["plages"]->loadList($where, $order);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPrats", $listPrats);
$smarty->assign("filter", $filter);
$smarty->assign("list", $list);
$smarty->assign("total", $total);
$smarty->assign("today", CMbDT::date());

$smarty->display("view_compta.tpl");