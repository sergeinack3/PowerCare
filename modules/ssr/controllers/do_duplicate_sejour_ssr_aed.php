<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;

$sejour_id          = CValue::post("sejour_id");
$original_sejour_id = CValue::post("original_sejour_id");
$module             = CValue::post('module');

$sejour = new CSejour();
$sejour->load($sejour_id);

// Chargement de la prescription de sejour
$sejour->loadRefPrescriptionSejour();

// Verification que la prescription est vide
if ($sejour->_ref_prescription_sejour->countBackRefs("prescription_line_element")) {
  CAppUI::setMsg(CAppui::tr("ssr-no_duplicate_sejour_with_prescription"), UI_MSG_WARNING);
  CAppUI::redirect('m='.$module.'&tab=vw_aed_sejour_ssr&sejour_id=' . $sejour_id);
}

// Chargement du sejour à dupliquer
$original_sejour = new CSejour();
$original_sejour->load($original_sejour_id);

// Chargement des references: bilan, fiche d'autonomie, prescriptions, evenements
$bilan_ssr           = $original_sejour->loadRefBilanSSR();
$fiche_autonomie     = $original_sejour->loadRefFicheAutonomie();
$prescription_sejour = $original_sejour->loadRefPrescriptionSejour();
$lines_element       = $prescription_sejour->loadRefsLinesElement();

// Chargement evenements de la derniere semaine complete
$original_last_friday = CMbDT::date("last friday", CMbDT::date("+ 1 DAY", $original_sejour->sortie));
$monday               = CMbDT::date("last monday", $original_last_friday);
$next_monday          = CMbDT::date("next monday", $monday);

// 1er vendredi du nouveau sejour
$next_friday = CMbDT::date("next friday", CMbDT::date("- 1 DAY", $sejour->entree));

// Calcul du nombre de decalage entre les 2 sejours
$nb_decalage = CMbDT::daysRelative($original_last_friday, $next_friday);

$evenement_ssr      = new CEvenementSSR();
$where              = array();
$where["sejour_id"] = " = '$original_sejour->_id'";
$where["debut"]     = " BETWEEN '$monday' AND '$next_monday'";

/** @var CEvenementSSR[] $evenements */

$evenements = $evenement_ssr->loadList($where);

// Chargement des refs du sejour actuel et suppression des objets existants
$sejour->loadRefBilanSSR();
if ($sejour->_ref_bilan_ssr->_id) {
  $msg = $sejour->_ref_bilan_ssr->delete();
  CAppUI::displayMsg($msg, "CBilanSSR-msg-delete");
}

$sejour->loadRefFicheAutonomie();
if ($sejour->_ref_fiche_autonomie->_id) {
  $msg = $sejour->_ref_fiche_autonomie->delete();
  CAppUI::displayMsg($msg, "CFicheAutonomie-msg-delete");
}

if ($sejour->_ref_prescription_sejour->_id) {
  $msg = $sejour->_ref_prescription_sejour->delete();
  CAppUI::displayMsg($msg, "CPrescription-msg-delete");
}

// Duplication du bilan
if ($module != "psy") {
    $bilan_ssr->_id       = "";
    $bilan_ssr->sejour_id = $sejour_id;
    $msg                  = $bilan_ssr->store();
    CAppUI::displayMsg($msg, "CBilanSSR-msg-create");
    // Duplication de la fiche d'autonomie
    $fiche_autonomie->_id       = "";
    $fiche_autonomie->sejour_id = $sejour_id;
    $msg                        = $fiche_autonomie->store();
    CAppUI::displayMsg($msg, "CFicheAutonomie-msg-create");
}
// Duplication de la prescription
$prescription_sejour->_id       = "";
$prescription_sejour->object_id = $sejour_id;
$msg                            = $prescription_sejour->store();
CAppUI::displayMsg($msg, "CPrescription-msg-create");

$original_to_new_line = array();
foreach ($lines_element as $_line_element) {
  $original_line_element_id = $_line_element->_id;

  $_line_element->_id             = "";
  $_line_element->prescription_id = $prescription_sejour->_id;
  $msg                            = $_line_element->store();
  CAppUI::displayMsg($msg, "$_line_element->_class-msg-create");

  $original_to_new_line[$original_line_element_id] = $_line_element->_id;
}

// Duplication des evenements et des actes associés
foreach ($evenements as $_evenement) {
  $actes_cdarrs = $_evenement->loadRefsActesCdARR();
  $actes_csarrs = $_evenement->loadRefsActesCsARR();

  $_evenement->_id                          = "";
  $_evenement->sejour_id                    = $sejour_id;
  $_evenement->realise                      = "0";
  $_evenement->annule                       = "0";
  $_evenement->prescription_line_element_id = $original_to_new_line[$_evenement->prescription_line_element_id];
  $_evenement->debut                        = CMbDT::dateTime("+ $nb_decalage DAYS", $_evenement->debut);
  $msg                                      = $_evenement->store();
  CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");

  foreach ($actes_cdarrs as $_acte) {
    $_acte->_id              = "";
    $_acte->evenement_ssr_id = $_evenement->_id;
    $msg                     = $_acte->store();
    CAppUI::displayMsg($msg, "CActeCdARR-msg-create");
  }

  foreach ($actes_csarrs as $_acte) {
    $_acte->_id              = "";
    $_acte->evenement_ssr_id = $_evenement->_id;
    $msg                     = $_acte->store();
    CAppUI::displayMsg($msg, "CActeCsARR-msg-create");
  }
}

CAppUI::redirect('m=' . $module .'&tab=vw_aed_sejour_ssr&sejour_id=' . $sejour_id);
