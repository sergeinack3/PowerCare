<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;

CCanDo::checkRead();

$dialog       = CValue::get("dialog");
$start_corres = CValue::get("start_corres", 0);
$step_corres  = CValue::get("step_corres", 20);

// Récuperation des correspondants recherchés
$corres_nom         = CValue::get("nom", "");
$corres_prenom      = CValue::get("prenom", "");
$corres_surnom      = CValue::get("surnom", "");
$corres_cp          = CValue::get("cp");
$corres_ville       = CValue::get("ville");
$corres_relation    = CValue::get("relation");

if (!$dialog) {
  CValue::setSession("correspondant_nom", $corres_nom);
  CValue::setSession("correspondant_prenom", $corres_prenom);
  CValue::setSession("correspondant_prenom", $corres_surnom);
  CValue::setSession("correspondant_cp", $corres_cp);
  CValue::setSession("correspondant_ville", $corres_ville);
  CValue::setSession("correspondant_relation", $corres_relation);
}

$correspondant = new CCorrespondantPatient();
$ds            = $correspondant->getDS();

$where               = array();
$where["patient_id"] = "IS NULL";

$current_user = CMediusers::get();
$is_admin     = $current_user->isAdmin();

if (CAppUI::isCabinet()) {
  $where["function_id"] = "= '$current_user->function_id'";
}
elseif (CAppUI::isGroup()) {
  $where["group_id"] = "= '" . $current_user->loadRefFunction()->group_id . "'";
}

if ($corres_nom) {
  $where["nom"] = $ds->prepareLike("%$corres_nom%");
}

if ($corres_prenom) {
  $where["prenom"] = $ds->prepareLike("%$corres_prenom%");
}
if ($corres_surnom) {
  $where["surnom"] = $ds->prepareLike("%$corres_surnom");
}

if ($corres_relation) {
  $where["relation"] = " = '$corres_relation'";
}

if ($corres_cp && $corres_cp != "00") {
  $cps = preg_split("/\s*[\s\|,]\s*/", $corres_cp);
  CMbArray::removeValue("", $cps);

  $where_cp = array();
  foreach ($cps as $cp) {
    $where_cp[] = "cp LIKE '" . $cp . "%'";
  }

  $where[] = implode(" OR ", $where_cp);
}

if ($corres_ville) {
  $where["ville"] = $ds->prepareLike("%$corres_ville%");
}

$order = "surnom, nom";

$nb_correspondants = $correspondant->countList($where);
/** @var CCorrespondantPatient[] $correspondants */
$correspondants = $correspondant->loadList($where, $order, "$start_corres, $step_corres");
foreach ($correspondants as $_corresp) {
  $_corresp->loadRefFunction();
}


$smarty = new CSmartyDP();

$smarty->assign("is_admin", $is_admin);
$smarty->assign("nb_correspondants", $nb_correspondants);
$smarty->assign("correspondants", $correspondants);
$smarty->assign("start_corres", $start_corres);
$smarty->assign("step_corres", $step_corres);

$smarty->display("inc_list_correspondants_modele.tpl");
