<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientSignature;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkRead();

$date_suivi        = CView::get("date", "date default|now");
$blocs_ids         = CView::get("blocs_ids", "str", true);
$salle_ids         = CView::get("salle_ids", "str");
$page              = CView::get("page", "num");
$mode_presentation = CView::get("mode_presentation", "bool default|0");
$view_light        = CView::get("view_light", "bool default|0");

CView::checkin();

if (!count($blocs_ids)) {
  CAppUI::stepAjax("dPbloc-msg-select_bloc", UI_MSG_WARNING);
  return;
}

$salles_ids = array();
if ($salle_ids) {
  $salles_ids = explode("-", $salle_ids);
}

$group = CGroups::loadCurrent();

$bloc = new CBlocOperatoire();
$whereBloc = array();
$whereBloc["bloc_operatoire_id"] = CSQLDataSource::prepareIn($blocs_ids);
$whereBloc["actif"] = " = '1'";
$blocs = $bloc->loadList($whereBloc);

$listBlocs = array();

if (CAppUI::gconf("dPsalleOp COperation allow_change_room")) {
  $listBlocs = $group->loadBlocs(PERM_READ, null, "nom", array("actif" => "= '1'"));
  // Chargement de la liste des salles de chaque bloc
  foreach ($listBlocs as $_bloc) {
    $_bloc->loadRefsSalles(array("actif" => "= '1'"));
  }
}

// Chargement des Chirurgiens
$chir      = new CMediusers();
$listChirs = $chir->loadPraticiens(PERM_READ);
$user = CMediusers::get();
$check_planning_visibility = false;
if ($user->isChirurgien() || $user->isMedecin() || $user->isDentiste()) {
  $check_planning_visibility = true;
}

$salles = array();

foreach ($blocs as $bloc) {
  $salle = new CSalle();
  $where["bloc_id"] = " = '$bloc->_id'";
  $where["actif"]   = " = '1'";

  if (count($salles_ids)) {
    $where["salle_id"] = CSQLDataSource::prepareIn($salles_ids);
  }

  $bloc->_ref_salles = $salle->loadListWithPerms(PERM_READ, $where, "nom");
    // The 'array_merge' function doesn't keep numerical keys which cause error during the mass load.
  $salles = $salles + $bloc->_ref_salles;
}

$whereMassLoad = [
    "date" => CSQLDataSource::get("std")->prepare("= ?", $date_suivi),
];

$plages     = CStoredObject::massLoadBackRefs($salles, "plages_op", null, $whereMassLoad);
$operations = CStoredObject::massLoadBackRefs($plages, "operations");
$sejours    = CStoredObject::massLoadFwdRef($operations, "sejour_id");
$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
foreach ($patients as $_patient) {
    /** @var  $_patient CPatient */
    $_patient->_homonyme = count($_patient->getPhoning($date_suivi));
}

$total_salles         = count($salles);
$page_size            = CAppUI::gconf("dPbloc mode_presentation salles_count");
$page_count           = null;
$current_page         = null;
$planned_op_count     = 0;
$completed_op_count   = 0;
$in_progress_op_count = 0;

if ($page) {
  $page_count   = ceil($total_salles / $page_size);
  $current_page = (($page - 1) % $page_count);
  $slice        = $current_page * $page_size;
  $salles       = array_slice($salles, $slice, $page_size, true);
}

$systeme_materiel_expert = CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "expert";
$dmi_active = CModule::getActive("dmi") && CAppUI::gconf("dmi CDM active");
$multiple_label  = CAppUI::gconf("dPplanningOp COperation multiple_label");

foreach ($salles as $salle) {
  /** @var CSalle $salle */
  $salle->loadRefsForDay($date_suivi, false, true);
  $salle->_ref_lines_dm = array();

  foreach ($salle->_ref_plages as $_plage) {
    $_plage->_ref_lines_dm = array();

    /* Check the visibility conditions depending on the value of the surgeon's function permission,
       if the connected user is a also surgeon */
    if ($check_planning_visibility && $_plage->chir_id) {
      $permission = CPreferences::getPref('bloc_planning_visibility', $_plage->chir_id);
      $chir = $_plage->loadRefChir();

      if (($permission['used'] == 'restricted' && $user->_id != $_plage->chir_id)
          || ($permission['used'] == 'function' && $user->function_id != $chir->function_id)
      ) {
        $_plage->_ref_operations = array();
        continue;
      }
    }

    COperation::massCountActes($_plage->_ref_operations);

    if ($multiple_label) {
      CStoredObject::massLoadBackRefs($_plage->_ref_operations, "liaison_libelle", "numero");
    }

    foreach ($_plage->_ref_operations as $_operation) {
      if ($_operation->annulee && !CAppUI::pref('planning_bloc_show_cancelled_operations')) {
        continue;
      }

      $_operation->countActes();
      $_operation->canDo();
      if ($multiple_label) {
        $_operation->loadLiaisonLibelle();
      }
      if ($systeme_materiel_expert) {
        $besoins = $_operation->loadRefsBesoins();
        CStoredObject::massLoadFwdRef($besoins, "type_ressource_id");
        foreach ($besoins as $_besoin) {
          $_besoin->loadRefTypeRessource();
        }
      }

      if ($mode_presentation) {
        $_operation->loadRefBrancardage();
      }

      if ($dmi_active) {
        $salle->_ref_lines_dm = array_merge(
          $salle->_ref_lines_dm,
          $_operation->_ref_sejour->loadRefPrescriptionSejour()->loadRefsLinesDM()
        );

        $_plage->_ref_lines_dm = array_merge(
          $_plage->_ref_lines_dm,
          $_operation->_ref_sejour->_ref_prescription_sejour->_ref_lines_dm
        );
      }

      // Nombre d'interventions en cours (placées)
      if ($_operation->entree_salle && !$_operation->sortie_salle) {
        $in_progress_op_count++;
      }

      // Nombre interventions terminées (placées)
      if ($_operation->entree_salle && $_operation->sortie_salle) {
        $completed_op_count++;
      }

      // Nombre interventions prévues (placées)
      $planned_op_count++;
    }

    COperation::massCountActes($_plage->_unordered_operations);

    if ($multiple_label) {
      CStoredObject::massLoadBackRefs($_plage->_unordered_operations, "liaison_libelle", "numero");
    }

    foreach ($_plage->_unordered_operations as $_operation) {
      if ($_operation->annulee && !CAppUI::pref('planning_bloc_show_cancelled_operations')) {
        continue;
      }

      $_operation->countActes();
      $_operation->canDo();
      if ($multiple_label) {
        $_operation->loadLiaisonLibelle();
      }
      if ($mode_presentation) {
        $_operation->loadRefBrancardage();
      }
      if ($dmi_active) {
        $salle->_ref_lines_dm = array_merge(
          $salle->_ref_lines_dm,
          $_operation->_ref_sejour->loadRefPrescriptionSejour()->loadRefsLinesDM()
        );
      }

      // Nombre d'interventions en cours (non placées)
      if ($_operation->entree_salle && !$_operation->sortie_salle) {
        $in_progress_op_count++;
      }

      // Nombre interventions terminées (non placées)
      if ($_operation->entree_salle && $_operation->sortie_salle) {
        $completed_op_count++;
      }

      // Nombre interventions prévues (non placées)
      $planned_op_count++;
    }
  }

  COperation::massCountActes($salle->_ref_urgences);

  if ($multiple_label) {
    CStoredObject::massLoadBackRefs($salle->_ref_urgences, "liaison_libelle", "numero");
  }

  $salle->_ref_lines_dm_urgence = array();

  foreach ($salle->_ref_urgences as $_key => $_operation) {
    if ($_operation->annulee && !CAppUI::pref('planning_bloc_show_cancelled_operations')) {
      continue;
    }

    /* Check the visibility conditions depending on the value of the surgeon's function permission,
       if the connected user is a also surgeon */
    if ($check_planning_visibility && $_operation->chir_id) {
      $permission = CPreferences::getPref('bloc_planning_visibility', $_operation->chir_id);
      $chir = $_operation->loadRefChir();

      if (($permission['used'] == 'restricted' && $user->_id != $_operation->chir_id)
          || ($permission['used'] == 'function' && $user->function_id != $chir->function_id)
      ) {
        unset($salle->_ref_urgences[$_key]);
        continue;
      }
    }

    $_operation->countActes();
    $_operation->canDo();
    if ($multiple_label) {
      $_operation->loadLiaisonLibelle();
    }
    if ($systeme_materiel_expert) {
      $besoins = $_operation->loadRefsBesoins();
      CStoredObject::massLoadFwdRef($besoins, "type_ressource_id");
      foreach ($besoins as $_besoin) {
        $_besoin->loadRefTypeRessource();
      }
    }

    if ($mode_presentation) {
      $_operation->loadRefBrancardage();
    }

    if ($dmi_active) {
      $salle->_ref_lines_dm = array_merge(
        $salle->_ref_lines_dm,
        $_operation->_ref_sejour->loadRefPrescriptionSejour()->loadRefsLinesDM()
      );

      $salle->_ref_lines_dm_urgence = array_merge(
        $salle->_ref_lines_dm_urgence,
        $_operation->_ref_sejour->_ref_prescription_sejour->_ref_lines_dm
      );
    }

    // Nombre d'interventions en cours (urgences)
    if ($_operation->entree_salle && !$_operation->sortie_salle) {
      $in_progress_op_count++;
    }

    // Nombre interventions terminées (urgences)
    if ($_operation->entree_salle && $_operation->sortie_salle) {
      $completed_op_count++;
    }

    // Nombre interventions prévues (urgences)
    $planned_op_count++;
  }

  foreach ($salle->_ref_deplacees as $_operation) {
    $_operation->canDo();
  }
}

// Interventions hors plages non traitées
$op = new COperation();
$ljoin = array();
$ljoin["sejour"] = "operations.sejour_id = sejour.sejour_id";
$where = array();
$where["operations.date"]       = "= '$date_suivi'";
$where["operations.salle_id"]   = "IS NULL";
$where["operations.plageop_id"] = "IS NULL";
$where["sejour.group_id"]       = "= '".$group->_id."'";

/** @var COperation[] $non_traitees */
$non_traitees = $op->loadList($where, null, null, null, $ljoin);

CStoredObject::massLoadFwdRef($non_traitees, "chir_id");

COperation::massCountActes($non_traitees);

if ($multiple_label) {
  CStoredObject::massLoadBackRefs($non_traitees, "liaison_libelle", "numero");
}

foreach ($non_traitees as $_operation) {
  if ($_operation->annulee && !CAppUI::pref('planning_bloc_show_cancelled_operations')) {
    continue;
  }

  $_operation->loadRefChir();
  if (!$_operation->_ref_chir->canDo()->read) {
    unset($non_traitees[$_operation->_id]);
    continue;
  }
  $_operation->canDo();
  if ($multiple_label) {
    $_operation->loadLiaisonLibelle();
  }
  $_operation->loadRefPatient()->updateBMRBHReStatus($_operation);
  $_operation->countActes();
  $_operation->loadExtCodesCCAM();
  $_operation->loadRefPlageOp();
  if ($mode_presentation) {
    $_operation->loadRefBrancardage();
  }
  if ($dmi_active) {
    $_operation->_ref_sejour->loadRefPrescriptionSejour()->loadRefsLinesDM();
  }

  // Nombre d'interventions en cours (hors plage)
  if ($_operation->entree_salle && !$_operation->sortie_salle) {
    $in_progress_op_count++;
  }

  // Nombre interventions terminées (hors plage)
  if ($_operation->entree_salle && $_operation->sortie_salle) {
    $completed_op_count++;
  }

  // Nombre interventions prévues (hors plage)
  $planned_op_count++;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("vueReduite"           , true);
$smarty->assign("listBlocs"            , $listBlocs);
$smarty->assign("salles"               , $salles);
$smarty->assign("date_suivi"           , $date_suivi);
$smarty->assign("operation_id"         , 0);
$smarty->assign("non_traitees"         , $non_traitees);
$smarty->assign("page"                 , $page);
$smarty->assign("page_count"           , $page_count);
$smarty->assign("current_page"         , $current_page);
$smarty->assign("mode_presentation"    , $mode_presentation);
$smarty->assign("view_light"           , $view_light);
$smarty->assign("dmi_active"           , $dmi_active);
$smarty->assign("planned_op_count"     , $planned_op_count);
$smarty->assign("completed_op_count"   , $completed_op_count);
$smarty->assign("in_progress_op_count" , $in_progress_op_count);

$smarty->display("inc_suivi_salles.tpl");
