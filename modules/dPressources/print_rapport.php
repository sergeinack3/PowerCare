<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ressources\CPlageressource;

CCanDo::checkRead();

//Recuperation des identifiants pour les filtres
$filter            = new CPlageressource;
$filter->_date_min = CValue::getOrSession("_date_min", CMbDT::date());
$filter->_date_max = CValue::getOrSession("_date_max", CMbDT::date());
$filter->prat_id   = CValue::getOrSession("prat_id");
$filter->paye      = CValue::getOrSession("type");

$prat_id = CValue::get("prat_id", 0);
if (!$prat_id) {
  echo "Vous devez choisir un praticien valide";
  CApp::rip();
}

if ($filter->_date_max > CMbDT::date()) {
  $filter->_date_max = CMbDT::date();
}

$filter->paye = CValue::get("type", 0);
$total        = 0;

// Chargement du praticien
$prat = new CMediusers;
$prat->load($filter->prat_id);

// Chargement des plages de ressource
$plages = new CPlageressource;

$where["date"]    = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";
$where["prat_id"] = "= '$filter->prat_id'";
$where["paye"]    = "= '$filter->paye'";

$order = "date";

$plages = $plages->loadList($where, $order);

foreach ($plages as $key => $value) {
  $total += $value->tarif;
}

$smarty = new CSmartyDP();

$smarty->debugging = false;

$smarty->assign("filter", $filter);
$smarty->assign("prat", $prat);
$smarty->assign("plages", $plages);
$smarty->assign("total", $total);

$smarty->display("print_rapport.tpl");
