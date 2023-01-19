<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureEtablissement;

CCanDo::checkEdit();
// Récupération des paramètres
$date   = CView::get("date", "date");
$filter = new CPlageconsult();
$filter->_date_min = CView::get("_date_min", "date default|now", true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$filter->_type_affichage = CView::get("typeVue", "str default|1", true);
$chir_id = CView::get("chir", "str", true);
$cs      = CView::get("cs", "str", true);
$export_csv = CView::get("export_csv", "bool default|0");
CView::checkin();
CView::enableSlave();

// Traduction pour le passage d'un enum en bool pour les requetes sur la base de donnee
if ($filter->_type_affichage == "1") {
  $filter->_type_affichage = 1;
}
elseif ($filter->_type_affichage == "2") {
  $filter->_type_affichage = 0;
}
$ljoin = array();
$where = array();
$where["ouverture"] = "BETWEEN '$filter->_date_min 00:00:00' AND '$filter->_date_max 23:59:59'";

// Consultations gratuites
if (!$cs) {
  $where[] = "du_patient + du_tiers > 0";
}

if ($date) {
  //CSQLDataSource::$trace = true;
  $ljoin["facture_liaison"] = "facture_liaison.facture_id = facture_etablissement.facture_id";
  $ljoin["sejour"] = "facture_liaison.object_id = sejour.sejour_id";
  
  $where["facture_liaison.facture_class"] = " = 'CFactureEtablissement'";
  $where["facture_liaison.object_class"] = " = 'CSejour'";
  $where["sejour.sortie"] = " LIKE '%$date%'";
}

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

$where["facture_etablissement.praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

// Initialisation du tableau de reglements
$reglement = new CReglement();
$recapReglement["total"]      = array(
  "nb_sejours"           => "0",
  "reste_patient"        => "0",
  "reste_tiers"          => "0",
  "du_patient"           => "0",
  "du_tiers"             => "0",
  "nb_reglement_patient" => "0",
  "nb_reglement_tiers"   => "0",
  "nb_impayes_tiers"     => "0",
  "nb_impayes_patient"   => "0",
  "secteur1"             => "0",
  "secteur2"             => "0"
);

foreach (array_merge($reglement->_specs["mode"]->_list, array("")) as $_mode) {
  $recapReglement[$_mode] = array(
    "du_patient"           => "0",
    "du_tiers"             => "0",
    "nb_reglement_patient" => "0",
    "nb_reglement_tiers"   => "0"
  );
}

// Reglements via les factures d'établissement
$where["cloture"]    = "IS NOT NULL";
$where["facture_etablissement.patient_id"] = "IS NOT NULL";
$order = "ouverture, praticien_id";

$facture = new CFactureEtablissement();
$listFactures = $facture->loadGroupList($where, $order, null, "facture_id", $ljoin);
CFactureEtablissement::massCheckNumCompta($listFactures, $facture);

$use_cotation_ccam = CAppUI::gconf("dPccam codage use_cotation_ccam");
$listPlages = array();
foreach ($listFactures as $_facture) {
  /** @var CFacture $_facture*/
  $_facture->loadRefPatient();
  $_facture->loadRefPraticien();
  $_facture->loadRefsObjects();
  $_facture->loadRefsReglements();
  $_facture->loadRefsNotes();

  // Ajout de reglements
  $_facture->_new_reglement_patient["montant"] = $_facture->_du_restant_patient;
  $_facture->_new_reglement_tiers["montant"]  = $_facture->_du_restant_tiers;
  
  $recapReglement["total"]["nb_sejours"] += count($_facture->_ref_sejours);
  
  $recapReglement["total"]["du_patient"]      += $_facture->_reglements_total_patient;
  $recapReglement["total"]["reste_patient"]   += $_facture->_du_restant_patient;
  if ($_facture->_du_restant_patient) {
    $recapReglement["total"]["nb_impayes_patient"]++;
  }

  $recapReglement["total"]["du_tiers"]        += $_facture->_reglements_total_tiers;
  $recapReglement["total"]["reste_tiers"]     += $_facture->_du_restant_tiers;
  if ($_facture->_du_restant_tiers) {
    $recapReglement["total"]["nb_impayes_tiers"]++;
  }
  
  $recapReglement["total"]["nb_reglement_patient"] += count($_facture->_ref_reglements_patient);
  $recapReglement["total"]["nb_reglement_tiers"]   += count($_facture->_ref_reglements_tiers  );
  if ($use_cotation_ccam) {
    $recapReglement["total"]["secteur1"]             += $_facture->_secteur1;
    $recapReglement["total"]["secteur2"]             += $_facture->_secteur2;
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
  $plage = $_facture->_ref_last_sejour;
  if (!$plage->_id) {
    $plage = $_facture->_ref_last_consult->loadRefSejour();
  }
  if ($plage->_id) {
    $debut_plage = CMbDT::date($plage->sortie);
    if (!isset($listPlages["$debut_plage"])) {
      $listPlages["$debut_plage"]["plage"] = $plage;
      $listPlages["$debut_plage"]["total"]["secteur1"] = 0;
      $listPlages["$debut_plage"]["total"]["secteur2"] = 0;
      $listPlages["$debut_plage"]["total"]["total"]    = 0;
      $listPlages["$debut_plage"]["total"]["patient"]  = 0;
      $listPlages["$debut_plage"]["total"]["tiers"]    = 0;
    }
    
    $listPlages["$debut_plage"]["factures"][$_facture->_guid] = $_facture;
    if ($use_cotation_ccam) {
      $listPlages["$debut_plage"]["total"]["secteur1"] += $_facture->_secteur1;
      $listPlages["$debut_plage"]["total"]["secteur2"] += $_facture->_secteur2;
    }
    else {
      $listPlages["$debut_plage"]["total"]["secteur1"] += $_facture->_montant_sans_remise;
      $listPlages["$debut_plage"]["total"]["secteur2"] += $_facture->remise;
    }
    $listPlages["$debut_plage"]["total"]["total"]    += $_facture->_montant_avec_remise;
    $listPlages["$debut_plage"]["total"]["patient"]  += $_facture->_reglements_total_patient;
    $listPlages["$debut_plage"]["total"]["tiers"]    += $_facture->_reglements_total_tiers;
  }
}

$type_aff = 1;

if (!$export_csv) {
  // Création du template
  $smarty = new CSmartyDP();
  $smarty->assign("today"         , CMbDT::date());
  $smarty->assign("filter"        , $filter);
  $smarty->assign("listPrat"      , $listPrat);
  $smarty->assign("listPlages"    , $listPlages);
  $smarty->assign("recapReglement", $recapReglement);
  $smarty->assign("reglement"     , $reglement);
  $smarty->assign("type_aff"      , $type_aff);
  $smarty->display("print_actes.tpl");
}
else {
  $file = new CCSVFile();
  //Titres
  $titles = array(
    CAppUI::tr("CSejour-sortie_reelle"), CAppUI::tr("CFactureEtablissement"), CAppUI::tr("CFactureEtablissement-patient_id"),
    CAppUI::tr("CFactureEtablissement-praticien_id"), CAppUI::tr("CFactureItem-type")
  );
  if ($type_aff) {
    $titles2 = array(
      CAppUI::tr("CFactureEtablissement-_secteur1"), CAppUI::tr("CFactureEtablissement-_secteur2"), CAppUI::tr("CConsultation-_somme"),
      CAppUI::tr("CFactureEtablissement-du_patient"), CAppUI::tr("CFactureEtablissement-du_tiers"),
    );
  } else {
    $titles2 = array(
      CAppUI::tr("CFacture-montant"), CAppUI::tr("CFactureCabinet-remise"), CAppUI::tr("CConsultation-_somme"),
      CAppUI::tr("CFactureEtablissement-du_patient")
    );
  }
  $titles = array_merge_recursive($titles, $titles2);
  $titles[] = CAppUI::tr("CFactureCabinet-patient_date_reglement");

  $libelle_sejour = CAppUI::tr("CSejour");
  $libelle_from = CAppUI::tr("date.from");
  $libelle_to = CAppUI::tr("date.to");
  $libelle_operation = CAppUI::tr("COperation");
  $file->writeLine($titles);
  foreach ($listPlages as $_date => $_sejour) {
    foreach ($_sejour['factures'] as $_facture) {
      $plage = $_sejour['plage'];
      $line_actes = array(
        CMbDT::format($plage->sortie, CAppUI::conf("date")),
        $_facture->_view,
        $_facture->_ref_patient->_view,
        $_facture->_ref_praticien->_view,
      );
      $line_type = $libelle_sejour." $libelle_from ".CMbDT::format($plage->entree, CAppUI::conf("datetime"))." $libelle_to ";
      $line_type .= CMbDT::format($plage->sortie, CAppUI::conf("datetime"));
      if ($plage->_ref_operations) {
        foreach ($plage->_ref_operations as $_operation) {
          $line_type .= " - $libelle_operation $libelle_from ".CMbDT::format($_operation->date, CAppUI::conf("date"));
          if ($_operation->libelle) {
            $line_type .= " $_operation->libelle";
          }
        }
      }
      $plage->loadRefsConsultations();
      if ($plage->_ref_consultations) {
        foreach ($plage->_ref_consultations as $_consult) {
          $line_type .= " - ".CAppUI::tr("dPcabinet-Consultation of %s", CMbDT::format($_consult->_datetime, '%d %B %Y'));
          if ($_consult->motif) {
            $line_type .= " $_consult->motif";
          }
        }
      }
      $line_actes[] = $line_type;
      $line_actes[] = $_facture->_secteur1;
      $line_actes[] = $type_aff ? $_facture->_secteur2 : $_facture->remise;
      $line_actes[] = $_facture->_montant_avec_remise;
      $line_actes[] = abs($_facture->_du_restant_patient) > 0.001 ? $_facture->_new_reglement_patient["montant"] : "";
      if ($type_aff) {
        $line_actes[] = abs($_facture->_du_restant_tiers) > 0.001 ? $_facture->_new_reglement_tiers["montant"] : "";
      }
      $line_actes[] = $_facture->patient_date_reglement ? CMbDT::format($_facture->patient_date_reglement, CAppUI::conf("date")) : "";
      $file->writeLine($line_actes);
    }
  }
  $file_name = CAppUI::tr("Compta.valid_money") . "_";
  if ($chir_id && $listPrat[$chir_id]) {
    $file_name .= $listPrat[$chir_id]->_user_first_name . "_" . $listPrat[$chir_id]->_user_last_name . "_";
  }
  if ($filter->_date_min != $filter->_date_max) {
    $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y') . "_" . CAppUI::tr("date.to") . "_" . CMbDT::format($filter->_date_max, '%d-%m-%Y');
  } else {
    $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y');
  }

  $file->stream($file_name);
  CApp::rip();
}
