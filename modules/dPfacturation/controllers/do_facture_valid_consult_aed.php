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
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;

CCanDo::checkEdit();
$consultations_id  = CView::post("consultations_id", "str");
$cloture_last_bill = CView::post("cloture_last_bill", "bool default|0");
$chir_id           = CView::post("chir_id", "ref class|CMediusers");
CView::checkin();

$consultations_id = json_decode(utf8_encode(stripslashes($consultations_id)), true);

$consult_by_patient = array();
foreach ($consultations_id as $_consult) {
  if (!$_consult["checked"]) {
    continue;
  }
  $consult_id = $_consult["consult_id"];
  $consultation = new CConsultation();
  $consultation->load($consult_id);
  if (!$consultation->_id) {
    continue;
  }
  $consult_by_patient[$consultation->patient_id][$consult_id] = $consultation;
}

$where = array();
$where["cloture"] = "IS NULL";
$use_auto_cloture = CAppUI::gconf("dPfacturation CFactureCabinet use_auto_cloture");
foreach ($consult_by_patient as $patient_id => $_consultations) {
  $nb_facture_not_cloture = 0;
  if (!$use_auto_cloture) {
    $where["patient_id"] = " = '$patient_id'";
    $facture_cab = new CFactureCabinet();
    $nb_facture_not_cloture  = $facture_cab->countList($where);
  }

  $facture = null;
  foreach ($_consultations as $_consultation) {
    /* @var CConsultation $_consultation*/
    //Afin de créer la facture, nous validons la consultation
    $_consultation->valide = 1;
    if (!$_consultation->tarif) {
      $_consultation->tarif = "manuel";
    }
    $msg = $_consultation->store();
    CAppUI::displayMsg($msg, "$_consultation->_class-msg-modify");
    if (!$msg) {
      $facture = $_consultation->loadRefFacture();
    }
  }

  //Si aucune n'était ouverte avant le script, nous cloturons la facture créée
  if (!$use_auto_cloture && !$nb_facture_not_cloture && $facture) {
    $facture->cloture = 'now';
    $msg = $facture->store();
    CAppUI::displayMsg($msg, "$facture->_class-msg-modify");
  }
}

echo CAppUI::getMsg();
CApp::rip();
