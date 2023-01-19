<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

global $m;

CCanDo::checkRead();
$spec_show_cancelled_services = array(
  "bool",
  "default" => CAppUI::conf("ssr recusation view_services_inactifs")
);
$show_cancelled_services      = CView::get("show_cancelled_services", $spec_show_cancelled_services, true);
$date                         = CView::get("date", "date default|now", true);
$current_day                  = CView::get("current_day", "bool default|0", true);
$order_way                    = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col                    = CView::get("order_col", "str default|patient_id", true);
$show                         = CView::get("show", "str default|all", true);
// Filtre
$filter               = new CSejour();
$filter->service_id   = CView::get("service_id", "ref class|CService", true);
$filter->praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);
$filter->referent_id  = CView::get("referent_id", "ref class|CMediusers", true);
CView::checkin();

$group = CGroups::loadCurrent();
// Chargement des evenement SSR
if (!$current_day) {
  $monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
  $sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));
}
else {
  $monday = $sunday = $date;
}

//Recherche des séjours
$ljoin = array(
  "patients" => "sejour.patient_id = patients.patient_id"
);

$where                  = array();
$where["sejour.annule"] = " = '0'";
$where["sejour.type"]   = " = '$m'";

switch ($order_col) {
  default:
  case "patient_id":
    $order = "patients.nom $order_way, patients.prenom, sejour.entree";
    break;
  case "entree":
    $order = "sejour.entree $order_way, patients.nom, patients.prenom";
    break;
  case "sortie":
    $order = "sejour.sortie $order_way, patients.nom, patients.prenom";
    break;
  case "praticien_id":
    $order = "sejour.praticien_id $order_way, patients.nom, patients.prenom";
    break;
  case "libelle":
    $order = "sejour.libelle $order_way, patients.nom, patients.prenom";
    break;
  case "service_id":
    $order = "sejour.service_id $order_way, patients.nom, patients.prenom";
}

$sejour  = new CSejour();
$sejours = CSejour::loadListForDate($date, $where, $order, null, "sejour.sejour_id", $ljoin);

// Masquer les services inactifs
if (!$show_cancelled_services) {
  $service            = new CService();
  $service->group_id  = $group->_id;
  $service->cancelled = "1";
  $services_ids       = array_keys($service->loadMatchingList());

  foreach ($sejours as $_sejour) {
    if ($_sejour->service_id && in_array($_sejour->service_id, $services_ids)) {
      unset($sejours[$_sejour->_id]);
    }
  }
}

// Utilisation du style spécifique de l'ancien thème pour éviter une regression sur l'impression du plan de soins
$style = file_get_contents("style/mediboard_ext/main.css") .
  file_get_contents("style/mediboard_ext/oldTables.css");

$pdfmerger                  = new CMbPDFMerger();
$htmltopdf                  = new CHtmlToPDF();
$compte_rendu               = new CCompteRendu();
$compte_rendu->_page_format = "A4";
$compte_rendu->_orientation = "landscape";
$file                       = new CFile();
$paths                      = array();

$date_time = $date . " " . CMbDT::time();

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadBackRefs($sejours, "bilan_ssr");
CSejour::massLoadCurrAffectation($sejours, $date_time);
// Filtres des séjours
foreach ($sejours as $_sejour) {
  // Filtre sur service
  $service_id = $_sejour->_ref_curr_affectation->service_id;
  if (!$service_id) {
    $service_id = $_sejour->service_id;
  }
  if ($filter->service_id && $service_id != $filter->service_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Filtre sur prescription, pas nécessairement actif
  $prescription = $_sejour->loadRefPrescriptionSejour();
  if ($show == "nopresc" && $prescription && $prescription->_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Filtre sur praticien
  if ($filter->praticien_id && $_sejour->praticien_id != $filter->praticien_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Bilan SSR
  $bilan = $_sejour->loadRefBilanSSR();
  // Kinés référent et journée
  $bilan->loadRefKineJournee($date);
  $kine_journee_id  = $bilan->_ref_kine_journee->_id;
  $kine_referent_id = $bilan->_ref_kine_referent->_id;
  if ($filter->referent_id && $kine_referent_id != $filter->referent_id && $kine_journee_id != $filter->referent_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
}

$days                = $current_day ? array(CMbDT::format($date, "%w") => array()) :
  array_fill_keys(array_keys(array_fill(1, 7, 0)), array());
$where_evts          = array();
$where_evts["debut"] = "BETWEEN '$monday 00:00:00' AND '$sunday 23:59:59'";

/* @var CSejour[] $sejours */
CMbObject::massLoadFwdRef($sejours, "patient_id");
$evenements_ssr = CMbObject::massLoadBackRefs($sejours, "evenements_ssr", "debut", $where_evts);
CMbObject::massLoadFwdRef($evenements_ssr, "prescription_line_element_id");
CMbObject::massLoadFwdRef($evenements_ssr, "equipement_id");
$seances_collectives = CMbObject::massLoadFwdRef($evenements_ssr, "seance_collective_id");
CMbObject::massLoadFwdRef($seances_collectives, "equipement_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefsEvtsSSRSejour($where_evts);
  $evenements_by_type = array("Matin" => $days, "Apres-midi" => $days);
  foreach ($_sejour->_ref_evts_ssr_sejour as $_evenement) {
    $_evenement->loadRefPrescriptionLineElement();
    $_evenement->loadRefEquipement();
    if ($_evenement->seance_collective_id) {
      $_evenement->debut = null;
      $_evenement->loadView();
      $_evenement->_ref_equipement = $_evenement->loadRefSeanceCollective()->loadRefEquipement();
    }
    $type = "Apres-midi";
    if (CMbDT::format($_evenement->_heure_fin, "%N") <= 12 && CMbDT::format($_evenement->debut, "%H") < 12) {
      $type = "Matin";
    }
    $evenements_by_type[$type][CMbDT::format($_evenement->debut, "%w")][] = $_evenement;
  }

  // Création du template
  $smarty = new CSmartyDP("modules/ssr");
  $smarty->assign("date", $date);
  $smarty->assign("sejour", $_sejour);
  $smarty->assign("group", $group);
  $smarty->assign("evenements", $evenements_by_type);
  $smarty->assign("style", $style);
  $smarty->assign("monday", $monday);
  $smarty->assign("sunday", $sunday);
  $smarty->assign("num_week", CMbDT::format($date, "%W"));
  $smarty->assign("current_day", $current_day);

  $content = $smarty->fetch("print_planning_sejour_to_pdf.tpl");

  $path             = tempnam("./tmp", 'ssr');
  $paths[]          = $path;
  $file->_file_path = $path;
  $file->fillFields();

  // Génération du PDF
  $pdf_content = $htmltopdf->generatePDF($content, 0, $compte_rendu, $file);
  file_put_contents($file->_file_path, $pdf_content);

  //Ajout au pdf général
  $pdfmerger->addPDF($file->_file_path);
}

foreach ($paths as $_path) {
  unlink($_path);
}

$pdfmerger->merge('browser', 'Impression des plannings.pdf');
CApp::rip();
