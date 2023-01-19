<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CGestePerop;

$operation_id    = CView::post("operation_id", "ref class|COperation");
$patient_id      = CView::post("patient_id", "ref class|CPatient");
$incident        = CView::post("incident", "bool default|0");
$datetime        = CView::post("datetime", "dateTime");
$geste_perop_ids = CView::post("_geste_perop_ids", "str");
$antecedent      = CView::post("_antecedent", "bool default|0");
$codecim         = CView::post("codecim", "str");
$gestes_datas    = CView::post("gestes", "str");
$context_menu    = CView::post("context_menu", "bool default|0");
CView::checkin();

$counter      = 0;
$current_user = CMediusers::get();

$operation = COperation::findOrNew($operation_id);
$patient   = $operation->loadRefPatient();

if (!$context_menu) {
  $gestes_ids = explode("|", $geste_perop_ids);

  foreach ($gestes_ids as $_geste_id) {
    $geste = new CGestePerop();
    $geste->load($_geste_id);

    if ($antecedent) {
      // Patient
      if ($patient_id) {
        $atcd = new CAntecedent();
        $atcd->type = "anesth";
        $atcd->date = CMbDT::date();
        $atcd->rques = $codecim ? $geste->libelle . ' ' . $codecim : $geste->libelle;
        $atcd->dossier_medical_id = CDossierMedical::dossierMedicalId($patient_id, "CPatient");
        $atcd->store();
      }
    }

    if ($geste->_id) {
      $anesth_perop = new CAnesthPerop();
      $anesth_perop->libelle        = $geste->libelle;
      $anesth_perop->geste_perop_id = $geste->_id;
      $anesth_perop->categorie_id   = $geste->categorie_id;
      $anesth_perop->datetime       = $datetime;
      $anesth_perop->operation_id   = $operation_id;
      $anesth_perop->incident       = $incident;
      $anesth_perop->user_id        = $current_user->_id;

      if ($msg = $anesth_perop->store()) {
        return $msg;
      }
      $counter++;
    }
  }
}
else {
  $structure_gestes = array();

  foreach ($gestes_datas as $_datas) {
    $structure_gestes = explode("|", $_datas);

    $geste = CGestePerop::find($structure_gestes[0]);
    $count_values = count($structure_gestes);

    if ($geste->_id) {
      $anesth_perop = new CAnesthPerop();
      $anesth_perop->libelle        = $geste->libelle;
      $anesth_perop->geste_perop_id = $geste->_id;
      $anesth_perop->categorie_id   = $geste->categorie_id;
      $anesth_perop->datetime       = $datetime;
      $anesth_perop->operation_id   = $operation_id;
      $anesth_perop->user_id        = $current_user->_id;
      $anesth_perop->incident       = $geste->incident;

      if ($count_values >= 2) {
        $anesth_perop->geste_perop_precision_id = $structure_gestes[1];
      }

      if ($count_values >= 3) {
        $anesth_perop->precision_valeur_id = $structure_gestes[2];
      }

      if ($msg = $anesth_perop->store()) {
        return $msg;
      }

      if ($geste->antecedent_code_cim) {
        $atcd = new CAntecedent();
        $atcd->type = "anesth";
        $atcd->date = CMbDT::date();
        $atcd->rques = $geste->libelle . ' ' . $geste->antecedent_code_cim;
        $atcd->dossier_medical_id = CDossierMedical::dossierMedicalId($patient->_id, "CPatient");
        $atcd->store();
      }

      $counter++;
    }
  }
}

CAppUI::displayMsg($msg, CAppUI::tr("CAnesthPerop-msg-create") . " x $counter");

echo CAppUI::getMsg();
CApp::rip();
