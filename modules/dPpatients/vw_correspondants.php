<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;

$group = CGroups::loadCurrent();

$default_cp         = (CAppUI::pref("medecin_cps_pref")) ? CAppUI::pref("medecin_cps_pref") : $group->_cp_court;
$correspondant_id   = CValue::get("correspondant_id");
$medecin_id         = CValue::get("medecin_id");
$view_update        = CValue::get("view_update", "");
$all_correspondants = CValue::get("all_correspondants", 0);

$function      = new CFunctions();
$listFunctions = $function->loadListWithPerms(PERM_EDIT);

$dialog = CValue::get("dialog", 0);

if ($dialog) { //only get
    $start_med    = CValue::get("start_med", 0);
    $step_med     = CValue::get("step_med", $dialog ? 10 : 20);
    $start_corres = CValue::get("start_corres", 0);
    $step_corres  = CValue::get("step_corres", $dialog ? 10 : 20);

    $medecin_nom         = CValue::get("medecin_nom");
    $medecin_prenom      = CValue::get("medecin_prenom");
    $medecin_cp          = CValue::get("medecin_cp", $default_cp);
    $medecin_function_id = CValue::get("medecin_function_id");
    $medecin_ville       = CValue::get("medecin_ville");
    $medecin_type        = CValue::get("medecin_type");
    $medecin_discipline  = CValue::get("medecin_disciplines");

    $corres_nom         = CValue::get("correspondant_nom");
    $corres_prenom      = CValue::get("correspondant_prenom");
    $corres_surnom      = CValue::get("correspondant_surnom");
    $corres_cp          = CValue::get("correspondant_cp", $default_cp);
    $corres_function_id = CValue::get("correspondant_function_id");
    $corres_ville       = CValue::get("correspondant_ville");
    $corres_relation    = CValue::get("relation");
} else {
    $start_med    = CValue::getOrSession("start_med", 0);
    $step_med     = CValue::getOrSession("step_med", $dialog ? 10 : 20);
    $start_corres = CValue::getOrSession("start_corres", 0);
    $step_corres  = CValue::getOrSession("step_corres", $dialog ? 10 : 20);

    $medecin_nom         = CValue::getOrSession("medecin_nom");
    $medecin_prenom      = CValue::getOrSession("medecin_prenom");
    $medecin_cp          = CValue::getOrSession("medecin_cp", $default_cp);
    $medecin_function_id = CValue::getOrSession("medecin_function_id");
    $medecin_ville       = CValue::getOrSession("medecin_ville");
    $medecin_type        = CValue::getOrSession("medecin_type");
    $medecin_discipline  = CValue::getOrSession("medecin_disciplines");

    $corres_nom         = CValue::getOrSession("correspondant_nom");
    $corres_prenom      = CValue::getOrSession("correspondant_prenom");
    $corres_surnom      = CValue::getOrSession("correspondant_surnom");
    $corres_cp          = CValue::getOrSession("correspondant_cp", $default_cp);
    $corres_function_id = CValue::getOrSession("correspondant_function_id");
    $corres_ville       = CValue::getOrSession("correspondant_ville");
    $corres_relation    = CValue::getOrSession("relation");
}

//order
$order_way = CValue::getOrSession("order_way", "DESC");
$order_col = CValue::getOrSession("order_col", "ccmu");

$medecin              = new CMedecin();
$medecin->nom         = $medecin_nom;
$medecin->prenom      = $medecin_prenom;
$medecin->cp          = $medecin_cp;
$medecin->function_id = $medecin_function_id;
$medecin->type        = $medecin_type;
$medecin->disciplines = $medecin_discipline;

$correspondant              = new CCorrespondantPatient();
$correspondant->nom         = $corres_nom;
$correspondant->prenom      = $corres_prenom;
$correspondant->surnom      = $corres_surnom;
$correspondant->cp          = $corres_cp;
$correspondant->function_id = $corres_function_id;
$correspondant->ville       = $corres_ville;
$correspondant->relation    = $corres_relation;

$types = $medecin->_specs['type']->_locales;
ksort($types);

$current_user = CMediusers::get();
$is_admin     = $current_user->isAdmin();

// smarty
$smarty = new CSmartyDP();
$smarty->assign("medecin", $medecin);
$smarty->assign("medecin_id", $medecin_id);
$smarty->assign("correspondant", $correspondant);
$smarty->assign("correspondant_id", $correspondant_id);
$smarty->assign("listFunctions", $listFunctions);
$smarty->assign("is_admin", $is_admin);
$smarty->assign("dialog", $dialog);
$smarty->assign("types", $types);
$smarty->assign("start_med", $start_med);
$smarty->assign("step_med", $step_med);
$smarty->assign("start_corres", $start_corres);
$smarty->assign("step_corres", $step_corres);
$smarty->assign("start_corrs", $start_corres);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign("view_update", $view_update);
$smarty->assign("all_correspondants", $all_correspondants);

$smarty->display("vw_correspondants.tpl");
