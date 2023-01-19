<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

global $mode_maternite;

if (!isset($mode_maternite)) {
  $mode_maternite = false;
}

$mediuser = CMediusers::get();

//Initialisations des variables
$cabinet_id   = CView::get("cabinet_id", "ref class|CFunctions default|".$mediuser->function_id, true);
$date         = CView::get("date", "date default|now", true);

$canceled       = CView::get("canceled" , "bool default|0", true);
$finished       = CView::get("finished" , "bool default|1", true);
$paid           = CView::get("paid"     , "bool default|1", true);
$empty          = CView::get("empty"    , "bool default|1", true);
$immediate      = CView::get("immediate", "bool default|1", true);
$mode_vue       = CView::get("mode_vue" , "enum list|vertical|horizontal default|vertical", true);
$matin          = CView::get("matin"    , "bool default|1", true);
$apres_midi     = CView::get("apres_midi", "bool default|1", true);
$prats_selected = CView::get("prats_selected", "str", true);

$mode_urgence = CView::get("mode_urgence", "str");
$offline      = CView::get("offline"     , "bool default|0");

$hour         = CMbDT::time(null);
$board        = CView::get("board", "bool default|1");
$boardItem    = CView::get("boardItem", "bool default|1");

CView::checkin();


$consult      = new CConsultation();

$nb_anesth = 0;

$cabinets = CMediusers::loadFonctions(PERM_EDIT, null, "cabinet");

if ($mode_urgence) {
  $group = CGroups::loadCurrent();
  $cabinet_id = $group->service_urgences_id;
}

// Récupération de la liste des praticiens
$praticiens = array();
$cabinet = new CFunctions();

if ($mode_maternite) {
  $praticiens = $mediuser->loadListFromType(array("Sage Femme"));
}
elseif ($cabinet_id) {
  $praticiens = CConsultation::loadPraticiens(PERM_EDIT, $cabinet_id, null, true);
  $cabinet->load($cabinet_id);
}

// Récupération des plages de consultation du jour et chargement des références
$listPlages = array();

$heure_limit_matin = CAppUI::gconf("dPcabinet CPlageconsult hour_limit_matin");

$listPlage = new CPlageconsult();
$where = array(
  "chir_id" => CSQLDataSource::prepareIn(array_keys($praticiens)),
  "date"    => "= '$date'"
);

if ($matin && !$apres_midi) {
  // Que le matin
  $where["debut"] = "< '$heure_limit_matin:00:00'";
}
elseif ($apres_midi && !$matin) {
  // Que l'après-midi
  $where["debut"] = "> '$heure_limit_matin:00:00'";
}
elseif (!$matin && !$apres_midi) {
  // Ou rien
  $where["debut"] = "IS NULL";
}

// Praticiens qui ont des plages pour la journée
$chir_ids = $listPlage->loadColumn("chir_id", $where);

foreach ($praticiens as $_prat) {
  if (!in_array($_prat->_id, $chir_ids)) {
    unset($praticiens[$_prat->_id]);
  }
}

$prat_available = $praticiens;
if (!$prats_selected) {
  $prats_selected = array_keys($praticiens);
}
else {
  $prats_selected = explode("-", $prats_selected);
}

$where["chir_id"] = CSQLDataSource::prepareIn($prats_selected);

if ($mode_urgence && CAppUI::gconf("dPurgences Display limit_reconvocations")) {
    $where["function_id"] = "= '$cabinet_id'";
}

$plages = $listPlage->loadList($where, "debut");

CMbObject::massLoadRefsNotes($plages);

$where_consult = array();

if (!$canceled) {
  $where_consult["annule"] = "= '0'";
}

if (!$finished) {
  $where_consult["chrono"] = "!=  '" . CConsultation::TERMINE . "'";
}

$consults = CStoredObject::massLoadBackRefs($plages, "consultations", "heure", $where_consult);

// Préchargement de masse sur les consultations
CStoredObject::massLoadFwdRef($consults, "patient_id");
CStoredObject::massLoadFwdRef($consults, "sejour_id");
CStoredObject::massLoadFwdRef($consults, "categorie_id");

CMbObject::massCountDocItems($consults);
/** @var CConsultAnesth[] $dossiers */
$dossiers = CStoredObject::massLoadBackRefs($consults, "consult_anesth");
CMbObject::massCountDocItems($dossiers);

foreach ($praticiens as $_prat) {
  $listPlages[$_prat->_id] = array(
    "prat"         => $_prat,
    "plages"       => array(),
    "destinations" => array()
  );
}

foreach ($plages as $_plage) {
  $listPlages[$_plage->chir_id]["plages"][$_plage->_id] = $_plage;
}

foreach ($praticiens as $prat) {
  if (!count($listPlages[$prat->_id]["plages"])) {
    unset($praticiens[$prat->_id]);
    unset($listPlages[$prat->_id]);
    continue;
  }
  if ($prat->_user_type == 4) {
    $nb_anesth++;
  }
}

// Destinations : plages des autres praticiens
foreach ($listPlages as $key_prat => $infos_by_prat) {
  foreach ($listPlages as $key_other_prat => $infos_other_prat) {
    if ($infos_by_prat["prat"]->_id != $infos_other_prat["prat"]->_id) {
      foreach ($listPlages[$key_other_prat]["plages"] as $key_plage => $other_plage) {
        $listPlages[$key_prat]["destinations"][] = $other_plage;
      }
    }
  }
}


$nb_attente = 0;
$nb_a_venir = 0;
$patients_fetch = array();

$heure_min = null;

foreach ($listPlages as $key_prat => $infos_by_prat) {
  foreach ($infos_by_prat["plages"] as $_plage) {
    $_plage->loadRefsNotes();

    /** @var CPlageconsult $_plage */
    $_plage->_ref_chir = $infos_by_prat["prat"];
    $_plage->loadRefsConsultations($canceled, $finished);
    // Collection par référence susceptible d'être modifiée
    $consultations =& $_plage->_ref_consultations;
    if (!$paid || !$immediate) {
      foreach ($consultations as $_consult) {
        if (!$paid) {
          $_consult->loadRefFacture()->loadRefsReglements();
          if ($_consult->valide == 1 && $_consult->_ref_facture->_du_restant_patient == 0) {
            unset($consultations[$_consult->_id]);
            continue;
          }
        }
        if (!$immediate && ($_consult->heure == CMbDT::time(null, $_consult->arrivee))
          && ($_consult->motif == "Consultation immédiate") && isset($consultations[$_consult->_id])) {
          unset($consultations[$_consult->_id]);
        }
      }
    }

    if (!count($consultations) && !$empty) {
      unset($listPlages[$key_prat]["plages"][$_plage->_id]);
      continue;
    }

    if (count($consultations) && $mode_vue == "horizontal") {
      $consultations = array_combine(range(0, count($consultations)-1), $consultations);
    }

    // Chargement du détail des consultations
    foreach ($consultations as $_consultation) {
      if ($mode_urgence) {
        $_consultation->getType();

        if (in_array($_consultation->_type, CSejour::getTypesSejoursUrgence($_plage->chir_id))) {
          unset($consultations[$_consultation->_id]);
          continue;
        }

        if ($_consultation->loadRefSejour()
            && $_consultation->_ref_sejour->loadRefRPUMutation()
            && $_consultation->_ref_sejour->_ref_rpu_mutation->rpu_id) {
            unset($consultations[$_consultation->_id]);
            continue;
        }
      }

      if ($heure_min === null) {
        $heure_min = $_consultation->heure;
      }

      if ($_consultation->heure < $heure_min) {
        $heure_min = $_consultation->heure;
      }

      if ($_consultation->chrono < CConsultation::TERMINE) {
        $nb_a_venir++;
      }

      if ($_consultation->chrono == CConsultation::PATIENT_ARRIVE) {
        $nb_attente++;
      }

      $_consultation->loadRefSejour();
      $_consultation->loadRefPatient();
      $_consultation->loadRefCategorie();
      $_consultation->loadRefPraticien();
      $_consultation->countDocItems();

      if (!$mode_urgence) {
        $_consultation->checkDHE($date);
      }

      if ($offline && $_consultation->patient_id && !isset($patients_fetch[$_consultation->patient_id])) {
        $args = array(
          "object_guid" => $_consultation->_ref_patient->_guid,
          "ajax" => 1,
        );

        $patients_fetch[$_consultation->patient_id] = CApp::fetch("system", "httpreq_vw_complete_object", $args);
      }
    }
  }
  if (!count($listPlages[$key_prat]["plages"]) && !$empty) {
    unset($listPlages[$key_prat]);
    unset($praticiens[$key_prat]);
  }
}

// Création du template
$smarty = new CSmartyDP("modules/dPcabinet");
$smarty->assign("offline"       , $offline);
$smarty->assign("cabinet_id"    , $cabinet_id);
$smarty->assign("cabinet"       , $cabinet);
$smarty->assign("patients_fetch", $patients_fetch);
$smarty->assign("consult"       , $consult);
$smarty->assign("listPlages"    , $listPlages);
$smarty->assign("empty"         , $empty);
$smarty->assign("canceled"      , $canceled);
$smarty->assign("paid"          , $paid);
$smarty->assign("finished"      , $finished);
$smarty->assign("immediate"     , $immediate);
$smarty->assign("date"          , $date);
$smarty->assign("hour"          , $hour);
$smarty->assign("praticiens"    , $praticiens);
$smarty->assign("praticiens_av" , $prat_available);
$smarty->assign("prats_selected", $prats_selected);
$smarty->assign("nb_anesth"     , $nb_anesth);
$smarty->assign("cabinets"      , $cabinets);
$smarty->assign("board"         , $board);
$smarty->assign("boardItem"     , $boardItem);
$smarty->assign("canCabinet"    , CModule::getCanDo("dPcabinet"));
$smarty->assign("mode_urgence"  , $mode_urgence);
$smarty->assign("mode_maternite", $mode_maternite);
$smarty->assign("nb_attente"    , $nb_attente);
$smarty->assign("nb_a_venir"    , $nb_a_venir);
$smarty->assign("mode_vue"      , $mode_vue);
$smarty->assign("heure_min"     , $heure_min);
$smarty->assign("matin"         , $matin);
$smarty->assign("apres_midi"    , $apres_midi);

$smarty->display("vw_journee");

