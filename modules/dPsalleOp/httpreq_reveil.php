<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PatientMonitoring\CMonitoringSession;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CDailyCheckList;

CCanDo::checkRead();
$date            = CView::get("date", "date default|now", true);
$bloc_id         = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
$sspi_id         = CView::get("sspi_id", "ref class|CSSPI", true);
$type            = CView::get("type", "enum list|preop|encours|ops|reveil|out"); // Type d'affichage => encours, ops, reveil, out
$modif_operation = CCanDo::edit() || $date >= CMbDT::date();

// orders & filters
$order_col = CView::get("order_col", "str");
if ($order_col && $type) {
  CView::setSession("order_col_" . $type, $order_col);
}

//order way
$order_way = CView::get("order_way", "str");
if ($order_way && $type) {
  CView::setSession("order_way_" . $type, $order_way);
}

$order_way_final = CView::get("order_way_".$type, "str default|$order_way", true);

switch ($type) {
  case "preop":
    $order_col = CView::get("order_col_$type", "str default|time_operation", true);
    break;
  case "encours":
    $order_col = CView::get("order_col_$type", "str default|entree_salle", true);
    break;
  case "ops":
    $order_col = CView::get("order_col_$type", "str default|sortie_salle", true);
    break;
  case "reveil":
    $order_col = CView::get("order_col_$type", "str default|entree_reveil", true);
    break;
  default:
    $order_col = CView::get("order_col_$type", "str default|sortie_reveil_possible", true);
    $order_way = @CView::get("order_way_$type", "str default|DESC", true);
    break;
}

if ($order_col === "_patient") {
  $order_col = "entree_salle";
}

//tri par patient
$order_col_type = $order_col;

CView::checkin();

$curr_user = CMediusers::get();
$group = CGroups::loadCurrent();

$use_poste = CAppUI::conf("dPplanningOp COperation use_poste");

// Selection des salles du bloc
$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

$salle = new CSalle();
$whereSalle = array("bloc_id" => " = '$bloc_id'");
$listSalles = $salle->loadListWithPerms(PERM_READ, $whereSalle);

// Selection des plages opératoires de la journée
$plage = new CPlageOp();
$where = array();
$where["date"] = "= '$date'";
// Filtre sur les salles qui pose problème
//$where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$plages = $plage->loadList($where);

$where = array();
$where["annulee"] = "= '0'";

$ljoin = array();

if ($use_poste && in_array($type, array("reveil", "out"))) {
  $ljoin["poste_sspi"] = "poste_sspi.poste_sspi_id = operations.poste_sspi_id";
  $where[] = "(operations.poste_sspi_id IS NOT NULL AND (poste_sspi.sspi_id = '$sspi_id' OR poste_sspi.sspi_id IS NULL))
           OR (operations.poste_sspi_id IS NULL AND (operations.sspi_id = '$sspi_id' OR operations.sspi_id IS NULL)
               AND operations.salle_id ". CSQLDataSource::prepareIn(array_keys($listSalles)) . ")";
}
else {
  $where["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
}
$where[] = "operations.plageop_id ".CSQLDataSource::prepareIn(array_keys($plages))." OR (operations.plageop_id IS NULL AND operations.date = '$date')";

switch ($type) {
  case 'preop':
    $where["operations.entree_salle"] = "IS NULL";
    $where["operations.sortie_salle"] = "IS NULL";
    break;

  case 'encours':
    $where["operations.entree_salle"] = "IS NOT NULL";
    $where["operations.sortie_salle"] = "IS NULL";
    break;

  case 'ops':
    $where["operations.sortie_salle"] = "IS NOT NULL";
    $where["operations.entree_reveil"] = "IS NULL";
    $where["operations.sortie_reveil_possible"] = "IS NULL";
    $where["operations.sortie_sans_sspi"] = "IS NULL";
    break;

  case 'reveil':
    //si preference à oui uniquement utilisateur courant (responsable SSPI)
    if (CAppUI::pref("pec_sspi_current_user")) {
      $ljoin["affectation_personnel"] = "affectation_personnel.object_id = operations.operation_id";
      $ljoin["personnel"]             = "personnel.personnel_id = affectation_personnel.personnel_id";
      $where[] = "affectation_personnel.object_class = 'COperation' AND personnel.user_id = '$curr_user->_id'";
    }

    $where["operations.entree_reveil"] = "IS NOT NULL";
    $where["operations.sortie_reveil_reel"] = "IS NULL";
    break;

  default:
    $where[] = "operations.sortie_reveil_reel IS NOT NULL OR operations.sortie_sans_sspi IS NOT NULL";
    break;
}

// Chargement des interventions
$operation = new COperation();
$listOperations = $operation->loadList($where, "$order_col $order_way_final", null, null, $ljoin);

// Optimisations de chargement
$chirs = CStoredObject::massLoadFwdRef($listOperations, "chir_id");
CStoredObject::massLoadFwdRef($chirs, "function_id");
CStoredObject::massLoadFwdRef($listOperations, "plageop_id");
CStoredObject::massCountBackRefs($listOperations, "notes");
if ($use_poste) {
  CStoredObject::massLoadFwdRef($listOperations, "poste_sspi_id");
  CStoredObject::massLoadFwdRef($listOperations, "poste_preop_id");
}

$anesths = CStoredObject::massLoadFwdRef($listOperations, "sortie_locker_id");
CStoredObject::massLoadFwdRef($anesths, "function_id");

if (in_array($type, array("ops", "reveil")) && CModule::getActive("bloodSalvage")) {
  CStoredObject::massCountBackRefs($listOperations, "blood_salvages");
}
$sejours  = CStoredObject::massLoadFwdRef($listOperations, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "dossier_medical");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CSejour::massLoadCurrAffectation(
    $sejours,
    $date . ($date === CMbDT::date() ? " " . CMbDT::time() : null)
);
CSejour::massLoadNDA($sejours);

$use_concentrator = CModule::getActive("patientMonitoring") && CAppUI::conf("patientMonitoring CMonitoringConcentrator active", $group);
if ($use_concentrator) {
  CStoredObject::massLoadBackRefs($listOperations, "monitoring_sessions");
}

$nb_sorties_non_realisees = 0;

$keywords = explode("|", CAppUI::conf("soins Other ignore_allergies", $group));

$atcd_absence         = array();
$perop_lines_unsigned = array();

/** @var COperation $op */
foreach ($listOperations as $op) {
  if (!$op->loadRefChir()->canDo()->read) {
    unset($listOperations[$op->_id]);
    continue;
  }

  $sejour = $op->loadRefSejour();

  if ($sejour->type === "exte") {
    unset($listOperations[$op->_id]);
    continue;
  }

  $op->_ref_chir->loadRefFunction();
  $op->loadRefPlageOp();
  $patient = $op->loadRefPatient();
  $patient->loadRefLatestConstantes(null, array("poids", "taille"));
  $patient->updateBMRBHReStatus($op);
  $dossier_medical = $patient->loadRefDossierMedical();
  if ($dossier_medical->_id) {
    $atcd_absence = $dossier_medical->loadRefsAntecedents(null, null, true, false, 1);

    $dossier_medical->loadRefsAntecedents(null, null, true, false, 0);
    $dossier_medical->loadRefsAllergies();
    $dossier_medical->countAntecedents(false, true);
    $dossier_medical->countAllergies();
  }

  $op->loadAffectationsPersonnel();
  $op->loadRefBrancardage();
  $op->loadRefsBrancardages();
  $op->loadRefsNotes();
  if ($use_poste) {
    $op->loadRefPoste();
    $op->loadRefPostePreop();
  }
  $op->loadRefSortieLocker()->loadRefFunction();

  if (in_array($type, array("ops", "preop")) && CModule::getActive("bloodSalvage")) {
    $salvage = $op->loadRefBloodSalvage();
    $salvage->loadRefPlageOp();
    $salvage->_totaltime = "00:00:00";
    if ($salvage->recuperation_start && $salvage->transfusion_end) {
      $salvage->_totaltime = CMbDT::timeRelative($salvage->recuperation_start, $salvage->transfusion_end);
    }
    elseif ($salvage->recuperation_start) {
      $from = $salvage->recuperation_start;
      $to   = CMbDT::date($salvage->_datetime)." ".CMbDT::time();
      $salvage->_totaltime = CMbDT::timeRelative($from, $to);
    }
  }

  if (in_array($type, array("out", "reveil"))) {
    if (!$op->sortie_reveil_reel) {
      $nb_sorties_non_realisees++;
    }
  }

  if ($use_concentrator) {
    $op->_active_session = CMonitoringSession::getCurrentSession($op);
  }

  $op->loadRefsConsultAnesth();

  // Line unsigned
  if ($type === "reveil") {
    $prescription = $sejour->loadRefPrescriptionSejour();
    $perop_lines_unsigned[$op->_id] = count($prescription->loadPeropLines($op->_id, false, null, null, false, 0));
  }
}

// Chargement de la liste du personnel pour le reveil
$personnels = array();
if (in_array($type, array("ops", "reveil")) && CModule::getActive("dPpersonnel")) {
  $personnel  = new CPersonnel();
  $personnels = $personnel->loadListPers("reveil", true, true);
}

// Vérification de la check list journalière
$daily_check_lists = array();
$daily_check_list_types = array();
$require_check_list = 0;
$require_check_list_close = 0;
$listChirs   = array();
$listAnesths = array();
$date_close_checklist = null;
$date_open_checklist  = null;

if ($type === "reveil" || $type === "preop") {
  $require_check_list = CAppUI::gconf("dPsalleOp CDailyCheckList active_salle_reveil") && $date >= CMbDT::date();
  $require_check_list_close = $require_check_list;
  $type_checklist = $type === "reveil" ? "ouverture_sspi" : "ouverture_preop";
  $type_close     = $type === "reveil" ? "fermeture_sspi" : "fermeture_preop";
  if ($require_check_list) {
    [
      $check_list_not_validated,
      $daily_check_list_types,
      $daily_check_lists
      ] = CDailyCheckList::getsCheckLists($bloc, $date, $type_checklist, null, $sspi_id);

    if ($check_list_not_validated == 0) {
      $require_check_list = false;
    }
  }
  if ($require_check_list_close) {
    [
      $check_list_not_validated_close,
      $daily_check_list_types_close,
      $daily_check_lists_close
      ] = CDailyCheckList::getsCheckLists($bloc, $date, $type_close, null, $sspi_id);

    if ($check_list_not_validated_close == 0) {
      $require_check_list_close = false;
    }
  }

  // Prise en compte de la sspi pour la récupération de la dernière saisie de checklist
    if ($sspi_id) {
        $sspi = new CSSPI();
        $sspi->load($sspi_id);

        $date_close_checklist = CDailyCheckList::getDateLastChecklist(
            $sspi,
            $type_close,
            true,
            $require_check_list_close
        );

        $date_open_checklist = CDailyCheckList::getDateLastChecklist($sspi, $type_checklist, true, $require_check_list);
    } else {
        // Si les sspi ne sont pas utilisées on vérifie sur le bloc
        $date_close_checklist = CDailyCheckList::getDateLastChecklist(
            $bloc,
            $type_close,
            true,
            $require_check_list_close
        );
        $date_open_checklist  = CDailyCheckList::getDateLastChecklist(
            $bloc,
            $type_checklist,
            true,
            $require_check_list
        );
    }

  if ($require_check_list) {
    // Chargement de la liste du personnel pour le reveil
    if (CModule::getActive("dPpersonnel") && !CAppUI::gconf("dPsalleOp CDailyCheckList choose_moment_edit")) {
      $type_personnel = array("reveil");
      if (count($daily_check_list_types)) {
        $type_personnel = array();
        foreach ($daily_check_list_types as $check_list_type) {
          $validateurs = explode("|", $check_list_type->type_validateur);
          foreach ($validateurs as $validateur) {
            $type_personnel[] = $validateur;
          }
        }
      }
      $personnel  = new CPersonnel();
      $personnels = $personnel->loadListPers(array_unique(array_values($type_personnel)), true, true);
    }
    $curr_user = CMediusers::get();
    // Chargement des praticiens
    $listChirs = $curr_user->loadPraticiens(PERM_DENY);
    // Chargement des anesths
    $listAnesths = $curr_user->loadAnesthesistes(PERM_DENY);
  }
}

//tri par patient
if ($order_col_type === "entree_salle") {
  CMbArray::pluckSort($listOperations, $order_way_final === "ASC" ? SORT_ASC : SORT_DESC, "_ref_patient", "nom");
  $order_col = $order_col_type;
}

$count_abs_allergie = 0;
if ($atcd_absence) {
  foreach ($atcd_absence as $_atcd_absence) {
    if ($_atcd_absence->type == 'alle') {
      $count_abs_allergie++;
    }
  }
}


// Création du template
$smarty = new CSmartyDP();
// Daily check lists
$smarty->assign("date_close_checklist"    , $date_close_checklist);
$smarty->assign("date_open_checklist"     , $date_open_checklist);
$smarty->assign("require_check_list"      , $require_check_list);
$smarty->assign("require_check_list_close", $require_check_list_close);
$smarty->assign("daily_check_lists"       , $daily_check_lists);
$smarty->assign("daily_check_list_types"  , $daily_check_list_types);
$smarty->assign("listChirs"               , $listChirs);
$smarty->assign("listAnesths"             , $listAnesths);
$smarty->assign("type"                    , $type);
$smarty->assign("bloc_id"                 , $bloc_id);
$smarty->assign("sspi_id"                 , $sspi_id);
$smarty->assign("personnels"              , $personnels);
$smarty->assign("order_way"               , $order_way);
$smarty->assign("order_col"               , $order_col);
$smarty->assign("listOperations"          , $listOperations);
$smarty->assign("plages"                  , $plages);
$smarty->assign("date"                    , $date);
$smarty->assign("isbloodSalvageInstalled" , CModule::getActive("bloodSalvage"));
$smarty->assign("modif_operation"         , $modif_operation);
$smarty->assign("isImedsInstalled"        , (CModule::getActive("dPImeds") && CImeds::getTagCIDC($group)));
$smarty->assign("nb_sorties_non_realisees", $nb_sorties_non_realisees);
$smarty->assign('atcd_absence'            , $atcd_absence);
$smarty->assign("count_abs_allergie"      , $count_abs_allergie);
$smarty->assign("perop_lines_unsigned"    , $perop_lines_unsigned);
$smarty->display("inc_reveil_$type");
