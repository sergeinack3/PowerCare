<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();

// Type d'affichage
$selAffichage  = CView::get("selAffichage", "str default|" . CAppUI::conf("dPurgences default_view"), true);
$service_id    = CView::get("service_id", "str", true);
$urgentiste_id = CView::get("urgentiste_id", "ref class|CMediusers", true);
$ccmu          = CView::get("ccmu", "str", true);
$cimu          = CView::get("cimu", "str", true);
$french_triage = CView::get("french_triage", "str", true);
$export        = CView::get("export", "bool default|0");

// Parametre de tri
$order_way         = CView::get("order_way", "str default|" . CAppUI::pref("chooseSortRPU"), true);
$order_col         = CView::get("order_col", "str default|" . CAppUI::pref("defaultRPUSort"), true);
$tri_reconvocation = CView::get("tri_reconvocation", "bool default|0", true);

// Selection de la date
$date           = CView::get("date", "date default|now", true);
$date_tolerance = CAppUI::conf("dPurgences date_tolerance");
$date_before    = CMbDT::date("-$date_tolerance DAY", $date);
$date_after     = CMbDT::date("+1 DAY", $date);
CView::checkin();

// L'utilisateur doit-il voir les informations médicales
$user        = CMediusers::get();
$medicalView = $user->isMedical() || $user->isFromType(array("Aide soignant"));

$group     = CGroups::loadCurrent();
$listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);

$prat_affectation = CAppUI::gconf("dPurgences CRPU prat_affectation");

$sejour                   = new CSejour();
$where                    = array();
$ljoin["rpu"]             = "sejour.sejour_id = rpu.sejour_id";
$where[]                  = CAppUI::pref("showMissingRPU") ?
  "sejour.type " . CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence()) . " OR rpu.rpu_id IS NOT NULL" :
  "rpu.rpu_id IS NOT NULL";

if ($urgentiste_id && !$prat_affectation) {
  $where["sejour.praticien_id"] = "= '$urgentiste_id'";
}

if ($ccmu) {
  $where["rpu.ccmu"] = "= '$ccmu'";
}

if ($cimu) {
  $where["rpu.cimu"] = "= '$cimu'";
}

if ($french_triage) {
  $where["rpu.french_triage"] = $sejour->getDS()->prepare("= ?", $french_triage);
}
/* @var CSejour[] $listSejours */
$listSejours = array();

foreach (
  array(
    "sejour.entree BETWEEN '$date' AND '$date_after'",
    "sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after'",
    "sejour.sortie_reelle BETWEEN '$date' AND '$date_after'"
  ) as $_where
) {
  $where[100] = $_where;

  switch ($selAffichage) {
    case "prendre_en_charge":
      $where["sejour.sortie_reelle"]         = "IS NULL";
      $where["sejour.annule"]                = "= '0'";
      $where["sejour.sejour_id"]             = "= rpu.mutation_sejour_id";
      $where["consultation.consultation_id"] = "IS NULL";
      $where["rpu.mutation_sejour_id"] = "IS NOT NULL";
      $ljoin["consultation"] = "consultation.sejour_id = rpu.mutation_sejour_id";
      //Première partie de la recherche
      $listSejours           += $sejour->loadGroupList($where, null, null, "sejour.sejour_id", $ljoin);
      unset($where["rpu.mutation_sejour_id"]);
      $where["sejour.sejour_id"]             = "= rpu.sejour_id";
      $where["rpu.mutation_sejour_id"]       = " IS NULL";
      $ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";
      break;
    case "presents":
      $where["sejour.sortie_reelle"] = "IS NULL";
      $where["sejour.annule"]        = " = '0'";
      if (CAppUI::conf("dPurgences create_sejour_hospit")) {
        $where["rpu.mutation_sejour_id"] = "IS NULL";
      }
      break;
    case "annule_hospitalise":
      $where[] = "(sejour.sortie_reelle IS NOT NULL AND sejour.mode_sortie  = 'mutation')
      OR (sejour.annule = '1')";
      break;
    case "pec":
      $where["sejour.annule"]                = "= '0'";
      $where["consultation.consultation_id"] = "IS NOT NULL";
      $ljoin["consultation"] = "consultation.sejour_id = rpu.mutation_sejour_id AND rpu.mutation_sejour_id IS NOT NULL";
      //Première partie de la recherche
      $listSejours           += $sejour->loadGroupList($where, null, null, "sejour.sejour_id", $ljoin);
      $ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";
      break;
    case "sortant":
      $where[] = "(sejour.sortie_reelle IS NOT NULL AND sejour.sortie_reelle  >= '".CMbDT::dateTime()."') OR rpu.sortie_autorisee = '1'";
      break;
    case "sortis":
      $where[] = "sejour.sortie_reelle IS NOT NULL AND sejour.sortie_reelle <= '".CMbDT::dateTime()."'";
      break;

    default:
      break;
  }
  //Recherche en fonction du filtre
  $listSejours += $sejour->loadGroupList($where, null, null, "sejour.sejour_id", $ljoin);
}

if ($service_id || ($prat_affectation && $urgentiste_id)) {
  CSejour::massLoadCurrAffectation($listSejours, $date);

  foreach ($listSejours as $key => $sejour) {
    $curr_aff = $sejour->_ref_curr_affectation;
    if ($service_id) {
      if ((!$curr_aff->_id && (!$sejour->service_id || $sejour->service_id != $service_id)) || $curr_aff->service_id != $service_id) {
        unset($listSejours[$key]);
      }
    }
    if ($prat_affectation && $urgentiste_id) {
      if (($curr_aff->_id && $curr_aff->praticien_id && $curr_aff->praticien_id != $urgentiste_id)
          || ((!$curr_aff->_id || !$curr_aff->praticien_id) && $sejour->praticien_id != $urgentiste_id)
      ) {
        unset($listSejours[$key]);
      }
    }
  }
}

CStoredObject::massLoadBackRefs($listSejours, "rpu");
$patients = CStoredObject::massLoadFwdRef($listSejours, "patient_id");

$prats = CStoredObject::massLoadFwdRef($listSejours, "praticien_id");
CStoredObject::massLoadFwdRef($prats, "function_id");
CStoredObject::massLoadFwdRef($listSejours, "etablissement_sortie_id");
CStoredObject::massLoadFwdRef($listSejours, "service_sortie_id");

CStoredObject::massLoadBackRefs($listSejours, "notes");

CStoredObject::massCountBackRefs($listSejours, "consultations");
CStoredObject::massCountBackRefs($listSejours, "prescriptions");

CMbobject::massCountDocItems($listSejours);

CSejour::massLoadCurrAffectation($listSejours);

CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

CSejour::massLoadNDA($listSejours);
CPatient::massLoadIPP($patients);

foreach ($listSejours as $sejour) {
  // Chargement du numero de dossier
  $sejour->loadRefPatient();
  $sejour->loadRefPraticien()->loadRefRemplacant($sejour->entree);

  $sejour->_ref_patient->updateBMRBHReStatus($sejour);

  /* @var CRPU $rpu */
  $rpu = $sejour->loadRefRPU();
  $rpu->loadRefSejour();
  $rpu->loadRefConsult();
  $rpu->loadRefMotifSFMU();
  $rpu->loadRefSejourMutation();
  $rpu->loadRefIDEResponsable();
  $rpu->loadRefsLastAttentes();
  $rpu->getColorCIMU();
  $last_reeval_pec = $rpu->loadRefLastReevaluationPec();

  if ($last_reeval_pec && $last_reeval_pec->_id) {
    $last_reeval_pec->getColorCIMUReevalPec();
  }

  $sejour->loadRefsConsultations();
  $sejour->loadRefsNotes();
  $sejour->_ref_curr_affectation->loadRefService();
  $sejour->_ref_curr_affectation->loadRefLit()->loadRefChambre();

  $prescription = $sejour->loadRefPrescriptionSejour();

  if ($prescription->_id) {
    if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
      $prescription->_count_fast_recent_modif = $prescription->countAlertsNotHandled("medium");
      $prescription->_count_urgence["all"]    = $prescription->countAlertsNotHandled("high");
    }
    else {
      $prescription->countFastRecentModif();
      $prescription->loadRefsLinesMedByCat();
      $prescription->loadRefsLinesElementByCat();
      $prescription->countUrgence($date);
    }
  }

  // Séjours antérieurs
  $sejour->_veille = CMbDT::date($sejour->entree) != $date;

  // Ajout des documents de la consultation dans le compteur
  $consult_atu = $sejour->_ref_consult_atu;

  if ($consult_atu->_id) {
    $sejour->_nb_files      += $consult_atu->_nb_files;
    $sejour->_nb_docs       += $consult_atu->_nb_docs;
    $sejour->_nb_files_docs += $consult_atu->_nb_files + $consult_atu->_nb_docs;

    CPrescription::$_load_lite = true;
    $consult_atu->loadRefsPrescriptions();
    CPrescription::$_load_lite = false;

    if (isset($consult_atu->_ref_prescriptions["externe"])) {
      $sejour->_nb_docs++;
      $sejour->_nb_files_docs++;
    }
  }
}

CPrescription::massLoadLinesElementImportant(
  array_combine(
    CMbArray::pluck($listSejours, "_ref_prescription_sejour", "_id"),
    CMbArray::pluck($listSejours, "_ref_prescription_sejour")
  )
);

CSejour::massLoadCurrAffectation($listSejours);
foreach ($listSejours as $key => $sejour) {
  $sejour->_ref_curr_affectation->loadRefPraticien();
}

// Tri des séjours
if (!in_array($order_col, array("_entree", "ccmu", "cimu", "french_triage", "_patient_id")) && !$tri_reconvocation) {
  $order_col = "ccmu";
}

$sort_1 = null;
$sort_2 = null;

switch ($order_col) {
  case "_entree":
    $sort_col_2 = (CAppUI::gconf("dPurgences CRPU french_triage")) ? "ccmu" : "french_triage";

    $sort_1 = CMbArray::pluck($listSejours, "entree");
    $sort_2 = CMbArray::pluck($listSejours, "_ref_rpu", $sort_col_2);
    break;
  case "ccmu":
    $sort_1 = CMbArray::pluck($listSejours, "_ref_rpu", "ccmu");
    $sort_2 = CMbArray::pluck($listSejours, "entree");
    break;
  case "cimu":
    $sort_1 = CMbArray::pluck($listSejours, "_ref_rpu", "cimu");
    $sort_2 = CMbArray::pluck($listSejours, "entree");
    break;
  case "french_triage":
    $sort_1 = CMbArray::pluck($listSejours, "_ref_rpu", "french_triage");
    $sort_2 = CMbArray::pluck($listSejours, "entree");
    break;
  case "_patient_id":
    $sort_1 = CMbArray::pluck($listSejours, "_ref_patient", "nom");
    $sort_2 = CMbArray::pluck($listSejours, "_ref_rpu", "ccmu");
}

if ($sort_1 && $sort_2) {
  array_multisort($sort_1, constant("SORT_$order_way"), $sort_2, constant("SORT_$order_way"), $listSejours);

  // Perte des clés avec le array_multisort
  foreach ($listSejours as $key => $_sejour) {
    $listSejours[$_sejour->_id] = $_sejour;
    unset($listSejours[$key]);
  }
}

// Chargement des boxes d'urgences
$boxes = array();
foreach (CService::loadServicesUrgence() as $service) {
  foreach ($service->_ref_chambres as $chambre) {
    foreach ($chambre->_ref_lits as $lit) {
      $boxes[$lit->_id] = $lit;
    }
  }
}
if (CAppUI::conf("dPurgences view_rpu_uhcd")) {
  foreach (CService::loadServicesUHCD() as $service) {
    foreach ($service->_ref_chambres as $chambre) {
      foreach ($chambre->_ref_lits as $lit) {
        $boxes[$lit->_id] = $lit;
      }
    }
  }
}
if (CAppUI::conf("dPurgences CRPU imagerie_etendue", $group)) {
  foreach (CService::loadServicesImagerie() as $_service) {
    foreach ($_service->_ref_chambres as $_chambre) {
      foreach ($_chambre->_ref_lits as $_lit) {
        $boxes[$_lit->_id] = $_lit;
      }
    }
  }
}

// Si admin sur le module urgences, alors modification autorisée du diagnostic
// infirmier depuis la main courante.
$admin_urgences = CModule::getCanDo("dPurgences")->admin;

//Reconvocations si config est en urg + consult
$consultations = array();

if (CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult") {
    $group = CGroups::loadCurrent();
    $cabinet_id = $group->service_urgences_id;
    $praticiens = [];
    $cabinet = new CFunctions();

    if ($cabinet_id) {
        $praticiens = CConsultation::loadPraticiens(PERM_EDIT, $cabinet_id, null, true);
        $cabinet->load($cabinet_id);
    }

    $plage = new CPlageconsult();
    $where = [
        "chir_id" => CSQLDataSource::prepareIn(array_keys($praticiens)),
        "date"    => "= '$date'",
    ];

    $chir_ids = $plage->loadColumn("chir_id", $where);

    foreach ($praticiens as $_prat) {
        if (!in_array($_prat->_id, $chir_ids)) {
            unset($praticiens[$_prat->_id]);
        }
    }

    $where["chir_id"] = CSQLDataSource::prepareIn(array_keys($praticiens));

    if (CAppUI::gconf("dPurgences Display limit_reconvocations")) {
        $where["function_id"] = "= '$cabinet_id'";
    }

    $plages = $plage->loadList($where, "debut");

    CStoredObject::massLoadFwdRef($plages, "chir_id");
    $consults = CStoredObject::massLoadBackRefs(
        $plages,
        "consultations",
        "heure",
        ["consultation.patient_id" => "IS NOT NULL"]
    );
    CStoredObject::massLoadFwdRef($consults, "patient_id");

    foreach ($plages as $_plage) {
        $consults = $_plage->loadRefsConsultations();

        foreach ($consults as $_consultation) {
            $_consultation->loadRefPatient();
            $_consultation->loadRefPraticien();
            $_consultation->getType();

            // Ne pas prendre en compte les séjours en hospi. complète
            // Pas de patients ayant un RPU associé au séjour, ni un séjour sur un autre établissement
            if (in_array($_consultation->_type, CSejour::getTypesSejoursUrgence()) ||
                ($_consultation->_ref_sejour && (($_consultation->_ref_sejour->type === "comp")))) {
                unset($consults[$_consultation->_id]);
                continue;
            }

            $consultations[$_consultation->_id] = $_consultation;
        }
    }

    if ($tri_reconvocation) {
        $sort = [];

        switch ($order_col) {
            case "heure":
                $sort = CMbArray::pluck($consultations, "heure");
                break;
            case "_patient_id":
                $sort = CMbArray::pluck($consultations, "_ref_patient", "nom");
        }

        if ($sort) {
            array_multisort($sort, constant("SORT_$order_way"), $consultations);
        }
    }
}

if (!$export) {
  // Création du template
  $smarty = new CSmartyDP();
  $smarty->assign("boxes"           , $boxes);
  $smarty->assign("order_col"       , $order_col);
  $smarty->assign("order_way"       , $order_way);
  $smarty->assign("listPrats"       , $listPrats);
  $smarty->assign("listSejours"     , $listSejours);
  $smarty->assign("selAffichage"    , $selAffichage);
  $smarty->assign("medicalView"     , $medicalView);
  $smarty->assign("date"            , $date);
  $smarty->assign("date_before"     , $date_before);
  $smarty->assign("today"           , CMbDT::date());
  $smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
  $smarty->assign("admin_urgences"  , $admin_urgences);
  $smarty->assign("type"            , "MainCourante");
  $smarty->assign("consultations"   , $consultations);
  $smarty->display("inc_main_courante");
}
else {
  CRPU::exportMainCourante($listSejours, $boxes, $date);
  CApp::rip();
}

