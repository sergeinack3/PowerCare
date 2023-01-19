<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();
// Récupération des paramètres
$filter = new CConsultation();
$filter->_date_min               = CView::get("_date_min", "date default|now", true);
$filter->_date_max               = CView::get("_date_max", "date default|now", true);
$filter->_etat_reglement_patient = CView::get("_etat_reglement_patient", "str", true);
$filter->_etat_reglement_tiers   = CView::get("_etat_reglement_tiers", "str", true);
$filter->_type_affichage         = CView::get("_type_affichage", "str default|1", true);
$all_impayes                     = CView::get("all_impayes", "bool default|0");
$chir_id                         = CView::getRefCheckRead("chir", "str", true);
$tarif_id                        = CView::get("tarif", "str", true);
CView::checkin();
CView::enableSlave();

// Traduction pour le passage d'un enum en bool pour les requetes sur la base de donnee
if ($filter->_type_affichage == "complete") {
  $filter->_type_affichage = 1;
}
elseif ($filter->_type_affichage == "totaux") {
  $filter->_type_affichage = 0;
}
$ljoin = array();
$where = array();
$where["ouverture"] = "BETWEEN '$filter->_date_min 00:00:00' AND '$filter->_date_max 23:59:59'";

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);
$where["facture_cabinet.praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

// Initialisation du tableau de reglements
$reglement = new CReglement();
$recapReglement["total"]      = array(
  "nb_evts"              => "0",
  "reste_patient"        => "0",
  "reste_tiers"          => "0",
  "du_patient"           => "0",
  "du_tiers"             => "0",
  "nb_reglement_patient" => "0",
  "nb_reglement_tiers"   => "0",
  "nb_impayes_tiers"     => "0",
  "nb_impayes_patient"   => "0",
  "secteur1"             => "0",
  "secteur2"             => "0",
  "secteur3"             => "0",
  "du_tva"               => "0"
);

foreach (array_merge($reglement->_specs["mode"]->_list, array("")) as $_mode) {
  $recapReglement[$_mode] = array(
    "du_patient"           => "0",
    "du_tiers"             => "0",
    "nb_reglement_patient" => "0",
    "nb_reglement_tiers"   => "0"
  );
}

// Etat des règlements
if ($all_impayes) {
  $where[] = "(facture_cabinet.patient_date_reglement IS NULL AND facture_cabinet.du_patient > 0)
    || (facture_cabinet.tiers_date_reglement IS NULL AND facture_cabinet.du_tiers > 0)";
}
else {
  if ($filter->_etat_reglement_patient == "reglee") {
    $where["patient_date_reglement"] = "IS NOT NULL";
  }
  if ($filter->_etat_reglement_patient == "non_reglee") {
    $where["patient_date_reglement"] = "IS NULL";
    $where["du_patient"] = "> 0";
  }

  if ($filter->_etat_reglement_tiers == "reglee") {
    $where["tiers_date_reglement"] = "IS NOT NULL";
  }
  if ($filter->_etat_reglement_tiers == "non_reglee") {
    $where["tiers_date_reglement"] = "IS NULL";
    $where["du_tiers"] = "> 0";
  }
}

// Reglements via les factures de consultation
$where["cloture"]    = "IS NOT NULL";
$where["facture_cabinet.patient_id"] = "IS NOT NULL";
$order = "ouverture, facture_cabinet.praticien_id";

// Tarifs
if ($tarif_id) {
  $tarif = new CTarif();
  $tarif->load($tarif_id);
  $ljoin["facture_liaison"]   = "facture_liaison.facture_id = facture_cabinet.facture_id";
  $ljoin["evenement_patient"] = "facture_liaison.object_id = evenement_patient.evenement_patient_id";

  $where["evenement_patient.tarif"] = " = '$tarif->description'";
}

$facture = new CFactureCabinet();
$listFactures = $facture->loadGroupList($where, $order, null, "facture_id", $ljoin);

$listEvt = array();
CMbObject::massLoadFwdRef($listFactures, "praticien_id");
CMbObject::massLoadFwdRef($listFactures, "patient_id");
CMbObject::massCountBackRefs($listFactures, "reglements");
CMbObject::massCountBackRefs($listFactures, "notes");

foreach ($listFactures as $_facture) {
  /* @var CFactureCabinet $_facture */
  $_facture->loadRefGroup();
  $_facture->loadRefPatient();
  $_facture->loadRefPraticien();
  $_facture->loadRefsEvenements();

  if (count($_facture->_ref_evts)) {
    $_facture->loadRefCoeffFacture();
    $_facture->updateMontants();
    $_facture->loadRefsReglements();
    $_facture->loadRefsNotes();
    if ($all_impayes) {
      $_facture->loadRefsRelances();
    }
    // Ajout de reglements
    $_facture->_new_reglement_patient["montant"] = $_facture->_du_restant_patient;
    $_facture->_new_reglement_tiers["montant"] = $_facture->_du_restant_tiers;

    $recapReglement["total"]["nb_evts"] += count($_facture->_ref_evts);

    $recapReglement["total"]["du_patient"]      += $_facture->_reglements_total_patient;
    $recapReglement["total"]["du_tva"]          += $_facture->du_tva;
    $recapReglement["total"]["reste_patient"]   += $_facture->_du_restant_patient;
    if ($_facture->_du_restant_patient && !$_facture->patient_date_reglement && $_facture->du_patient) {
      $recapReglement["total"]["nb_impayes_patient"]++;
    }

    $recapReglement["total"]["du_tiers"]        += $_facture->_reglements_total_tiers;
    $recapReglement["total"]["reste_tiers"]     += $_facture->_du_restant_tiers;
    if ($_facture->_du_restant_tiers) {
      $recapReglement["total"]["nb_impayes_tiers"]++;
    }

    $recapReglement["total"]["nb_reglement_patient"] += count($_facture->_ref_reglements_patient);
    $recapReglement["total"]["nb_reglement_tiers"]   += count($_facture->_ref_reglements_tiers  );
    if (CAppUI::gconf("dPccam codage use_cotation_ccam")) {
      $recapReglement["total"]["secteur1"]             += $_facture->_secteur1;
      $recapReglement["total"]["secteur2"]             += $_facture->_secteur2;
      $recapReglement["total"]["secteur3"]             += $_facture->_secteur3;
    }
    else {
      $recapReglement["total"]["secteur1"]             += $_facture->_montant_avec_remise;
    }

    foreach ($_facture->_ref_reglements_patient as $_reglement) {
      $recapReglement[$_reglement->mode]["du_patient"]          += $_reglement->montant;
      $recapReglement[$_reglement->mode]["nb_reglement_patient"]++;
    }

    foreach ($_facture->_ref_reglements_tiers as $_reglement) {
      $recapReglement[$_reglement->mode]["du_tiers"]          += $_reglement->montant;
      $recapReglement[$_reglement->mode]["nb_reglement_tiers"]++;
    }

    // Classement par plage
    /* @var CEvenementPatient $evt*/
    $evt = $_facture->_ref_last_evt;
    $evt->loadRefPraticien();
    if ($evt->_id) {
      $debut_plage = $evt->date;
      if (!isset($listEvt["$debut_plage"])) {
        $listEvt["$debut_plage"]["evt"] = $evt;
        $listEvt["$debut_plage"]["total"]["secteur1"] = 0;
        $listEvt["$debut_plage"]["total"]["secteur2"] = 0;
        $listEvt["$debut_plage"]["total"]["secteur3"] = 0;
        $listEvt["$debut_plage"]["total"]["du_tva"]   = 0;
        $listEvt["$debut_plage"]["total"]["total"]    = 0;
        $listEvt["$debut_plage"]["total"]["patient"]  = 0;
        $listEvt["$debut_plage"]["total"]["tiers"]    = 0;
      }

      $listEvt["$debut_plage"]["factures"][$_facture->_guid] = $_facture;
      $listEvt["$debut_plage"]["total"]["secteur1"] += $_facture->_secteur1;

      if (CAppUI::gconf("dPccam codage use_cotation_ccam")) {
        $listEvt["$debut_plage"]["total"]["secteur2"] += $_facture->_secteur2;
      }
      else {
        $listEvt["$debut_plage"]["total"]["secteur2"] += $_facture->remise;
      }
      $listEvt["$debut_plage"]["total"]["secteur3"] += $_facture->_secteur3;
      $listEvt["$debut_plage"]["total"]["du_tva"]   += $_facture->du_tva;
      $listEvt["$debut_plage"]["total"]["total"]    += $_facture->_montant_avec_remise;
      $listEvt["$debut_plage"]["total"]["patient"]  += $_facture->_reglements_total_patient;
      $listEvt["$debut_plage"]["total"]["tiers"]    += $_facture->_reglements_total_tiers;

    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("today"         , CMbDT::date());
$smarty->assign("filter"        , $filter);
$smarty->assign("listPrat"      , $listPrat);
$smarty->assign("listEvt"       , $listEvt);
$smarty->assign("recapReglement", $recapReglement);
$smarty->assign("reglement"     , $reglement);
$smarty->assign("all_impayes"   , $all_impayes);
$smarty->assign("nb_object"     , "nb_evts");
$smarty->assign("type_view"     , "evt");

$smarty->display("print_rapport_evt");
