<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$date        = CView::get("date", "date default|now", true);
$sejour_id   = CView::get("sejour_id", "ref class|CSejour", true);
$current_day = CView::get("current_day", "bool default|0");
$day_used    = CView::get("day_used", "date default|now", true);
$full_screen = CView::get("full_screen", "bool default|0");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadRefPraticien();
$bilan      = $sejour->loadRefBilanSSR();
$technicien = $bilan->loadRefTechnicien();
$technicien->loadRefKine();

// Chargement des evenement SSR 
$monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));
if ($current_day) {
  $monday = $sunday = $day_used;
}

$where              = array();
$where["sejour_id"] = " = '$sejour_id'";
$where["debut"]     = "BETWEEN '$monday 00:00:00' AND '$sunday 23:59:59'";

$evenement_ssr = new CEvenementSSR();
/** @var CEvenementSSR[] $evenements */
$evenements = $evenement_ssr->loadList($where, "debut", null, "evenement_ssr.evenement_ssr_id");

$elements     = array();
$intervenants = array();
foreach ($evenements as $_evenement) {
  if ($_evenement->seance_collective_id) {
    $_evenement->debut = null;
    $_evenement->loadView();
  }
  $element = $_evenement->loadRefPrescriptionLineElement()->_ref_element_prescription;
  $_evenement->loadRefTherapeute();
  $elements[$element->_id]                                 = $element;
  $intervenants[$element->_id][$_evenement->therapeute_id] = $_evenement->_ref_therapeute;
}
$group          = CGroups::loadCurrent();
$new_format_pdf = CAppUI::conf("ssr print_week new_format_pdf", $group);
if ($new_format_pdf) {
  $sejour->loadRefCurrAffectation()->updateView();
}
// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("date", $date);
$smarty->assign("elements", $elements);
$smarty->assign("intervenants", $intervenants);
$smarty->assign("sejour", $sejour);
$smarty->assign("current_day", $current_day);
$smarty->assign("full_screen", $full_screen);

if ($new_format_pdf & !$full_screen) {
  // Utilisation du style spécifique de l'ancien thème pour éviter une regression sur l'impression du plan de soins
  $style = file_get_contents("style/mediboard_ext/main.css") .
    file_get_contents("style/mediboard_ext/oldTables.css");

  $days = array_fill_keys(array_keys(array_fill(1, 7, 0)), array());
  if ($current_day) {
    $days = array(CMbDT::format($date, "%w") => array());
  }
  $evenements_by_type = array("Matin" => $days, "Apres-midi" => $days);
  foreach ($evenements as $_evenement) {
    $_evenement->loadRefEquipement();
    if ($_evenement->seance_collective_id) {
      $_evenement->_ref_equipement = $_evenement->loadRefSeanceCollective()->loadRefEquipement();
    }
    $type = "Apres-midi";
    if (CMbDT::format($_evenement->_heure_fin, "%N") <= 12 && CMbDT::format($_evenement->debut, "%H") < 12) {
      $type = "Matin";
    }
    $evenements_by_type[$type][CMbDT::format($_evenement->debut, "%w")][] = $_evenement;
  }

  foreach ($evenements_by_type as $type => $_evenements_by_data) {
    ksort($evenements_by_type[$type]);
  }

  $smarty->assign("group", $group);
  $smarty->assign("evenements", $evenements_by_type);
  $smarty->assign("style", $style);
  $smarty->assign("monday", $monday);
  $smarty->assign("sunday", $sunday);
  $smarty->assign("num_week", CMbDT::format($date, "%V"));

  $content = $smarty->fetch("print_planning_sejour_to_pdf.tpl");

  $file                       = new CFile();
  $compte_rendu               = new CCompteRendu();
  $compte_rendu->_page_format = "a4";
  $compte_rendu->_orientation = "landscape";
  // Génération du PDF
  $htmltopdf = new CHtmlToPDF();
  $pdf       = $htmltopdf->generatePDF($content, 1, $compte_rendu, $file);
}
else {
  $smarty->display("print_planning_sejour");
}
