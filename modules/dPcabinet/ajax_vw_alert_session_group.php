<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cerfa\Cerfa;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$consultation_id = CView::getRefCheckRead("consultation_id", "ref class|CConsultation");
$category_id     = CView::get("category_id", "ref class|CConsultationCategorie");
$patient_id      = CView::getRefCheckRead("patient_id ", "ref class|CPatient");
CView::checkin();

$consultation = new CConsultation();
$consultation->load($consultation_id);

CAccessMedicalData::logAccess($consultation);

$consultation_categorie = $consultation->loadRefCategorie();
$nb_consult             = $consultation_categorie->countRefConsultations($consultation->patient_id);
$threshold_alert        = $consultation_categorie->_threshold_alert;
$msg_alert              = null;

$patient = new CPatient();
$patient->load($consultation->patient_id ?: $patient_id);

if (!$consultation->_id) {
  $category = new CConsultationCategorie();
  $category->load($category_id);
  $nb_consult   = $category->countRefConsultations($patient_id);
  $threshold_alert  = $consultation_categorie->_threshold_alert;
}
if ($nb_consult >= $threshold_alert) {
  $msg_alert = CAppUI::tr('CConsultationCategorie-msg-Maximum number of sessions reached for this patient');
}
else {
  $msg_alert = CAppUI::tr('CConsultationCategorie-msg-Be careful, you will soon reach the maximum number of sessions (%s) Please complete a Cerfa Request for Prior Agreement to increase the number of sessions of %s', $max_seances, $patient->_view);
}
// Cerfa d'entente préalable
$list_cerfa = array();

if (CModule::getActive("ameli")) {
  $cerfa_active = CAppUI::gconf("cerfa General use_cerfa");

  if ($cerfa_active) {
    $entente_prealable = array("10522-01", "10524-01", "629-01-02", "12040-03");
    $list_cerfa        = Cerfa::getList();

    foreach ($list_cerfa as $_key => $_list) {
      if (!in_array($_key, $entente_prealable)) {
        unset($list_cerfa[$_key]);
      }
    }
  }
  else {
      CAppUI::stepMessage(UI_MSG_WARNING, "CCerfa-msg-You did not activate the configuration to use the Cerfa");
      CApp::rip();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("consultation", $consultation);
$smarty->assign("list_cerfa"  , $list_cerfa);
$smarty->assign("msg_alert"   , $msg_alert);
$smarty->display("inc_vw_alert_session_group");
