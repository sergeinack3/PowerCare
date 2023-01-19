<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CDailyCheckList;

CCanDo::checkRead();

$salle_id      = CView::get("salle", "ref class|CSalle", true);
$bloc_id       = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
$date          = CView::get("date", "date default|now", true);
$operation_id  = CView::get("operation_id", "ref class|COperation", true);
$hide_finished = CView::get("hide_finished", "bool default|0");

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

// Récuperation du service par défaut dans les préférences utilisateur
$group = CGroups::loadCurrent();
$group_id = $group->_id;
$default_salles_id = CAppUI::pref("default_salles_id");
// Récuperation de la salle à afficher par défaut
$default_salle_id = "";
$default_salles_id = json_decode($default_salles_id);
if (isset($default_salles_id->{"g$group_id"})) {
  $salle_ids = explode("|", $default_salles_id->{"g$group_id"});
  $default_salle_id = reset($salle_ids);
}

if (!$salle_id) {
  $salle_id = $default_salle_id;
}
// Chargement des praticiens
$currUser = CMediusers::get();
$listAnesths = $currUser->loadAnesthesistes(PERM_READ);

// Selection des salles
$listBlocs = $group->loadBlocs(PERM_READ, true, "nom", array("actif" => "= '1'"), array("actif" => "= '1'"));

// Selection des plages opératoires de la journée
$salle = new CSalle();
if ($salle->load($salle_id)) {
  $salle->loadRefsForDay($date, true);
}

if ($hide_finished == 1) {
  foreach ($salle->_ref_plages as $_plage) {
    foreach ($_plage->_ref_operations as $_key => $_op) {
      if ($_op->sortie_salle) {
        unset($_plage->_ref_operations[$_key]);
      }
    }
    foreach ($_plage->_unordered_operations as $_key => $_op) {
      if ($_op->sortie_salle) {
        unset($_plage->_unordered_operations[$_key]);
      }
    }
  }

  foreach ($salle->_ref_deplacees as $_key => $_op) {
    if ($_op->sortie_salle) {
      unset($salle->_ref_deplacees[$_key]);
    }
  }

  foreach ($salle->_ref_urgences as $_key => $_op) {
    if ($_op->sortie_salle) {
      unset($salle->_ref_urgences[$_key]);
    }
  }
}

// Calcul du nombre d'actes codé dans les interventions
if ($salle->_ref_plages) {
  foreach ($salle->_ref_plages as $_plage) {
    $operations = $_plage->_ref_operations;
    $operations = array_merge($operations, $_plage->_unordered_operations);

    COperation::massCountActes($operations);

    foreach ($_plage->_ref_operations as $_operation) {
      $_operation->countActes();
    }
    foreach ($_plage->_unordered_operations as $_operation) {
      $_operation->countActes();
    }
  }
}
if ($salle->_ref_deplacees) {
  COperation::massCountActes($salle->_ref_deplacees);
  foreach ($salle->_ref_deplacees as $_operation) {
    $_operation->countActes();
  }
}
if ($salle->_ref_urgences) {
  COperation::massCountActes($salle->_ref_urgences);
  foreach ($salle->_ref_urgences as $_operation) {
    $_operation->countActes();
  }
}

$date_last_checklist = null;
// Checklist_ouverture bloc manuelle
$choose_open_salle = CAppUI::conf("dPsalleOp CDailyCheckList choose_open_salle", $group);
if ($salle->cheklist_man || $choose_open_salle) {
  $date_last_checklist = CDailyCheckList::getDateLastChecklist($salle, "ouverture_salle");
}

// Checklist_fermeture bloc
$date_close_checklist = null;
$conf_required = $salle->_id ? $salle->loadRefBloc()->checklist_everyday : 0;
$require_check_list = ($conf_required || $salle->cheklist_man) && $date >= CMbDT::date() ? 1 : 0;

if ($require_check_list) {
  list($check_list_not_validated, $types, $lists) = CDailyCheckList::getCheckLists($salle, $date, "fermeture_salle");

  if ($check_list_not_validated == 0) {
    $require_check_list = false;
  }
  $date_close_checklist = CDailyCheckList::getDateLastChecklist($salle, "fermeture_salle");
}
if (!$date_close_checklist && $choose_open_salle) {
  $date_close_checklist = CDailyCheckList::getDateLastChecklist($salle, "fermeture_salle");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("default_salle_id", $default_salle_id);
$smarty->assign("group_id"      , $group_id);
$smarty->assign("vueReduite"    , false);
$smarty->assign("salle"         , $salle);
$smarty->assign("hide_finished" , $hide_finished);
$smarty->assign("praticien_id"  , null);
$smarty->assign("listBlocs"     , $listBlocs);
$smarty->assign("listAnesths"   , $listAnesths);
$smarty->assign("date"          , $date);
$smarty->assign("operation_id"  , $operation_id);
$smarty->assign("date_last_checklist", $date_last_checklist);
$smarty->assign("require_check_list_close", $require_check_list);
$smarty->assign("date_close_checklist", $date_close_checklist);

$smarty->display("inc_liste_plages.tpl");
