<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CExamPossum;

CCanDo::checkRead();

$consultation_id   = CValue::getOrSession("consultation_id");
$dossier_anesth_id = CValue::getOrSession("dossier_anesth_id");

$where = array("consultation_id" => "= '$consultation_id'");
$exam_possum = new CExamPossum;
$exam_possum->loadObject($where);
$exam_possum->loadRefsNotes();

if (!$exam_possum->_id) {
  $exam_possum->consultation_id = $consultation_id;
  $exam_possum->updateFormFields();
}

// Pré-remplissage de certaines valeurs
$consultation = $exam_possum->loadRefConsult();
$consultation->loadRefsFwd();
$consultation->loadRefConsultAnesth();
$dossier_anesth = $consultation->_refs_dossiers_anesth[$dossier_anesth_id];
$dossier_anesth->loadRefsFwd();

$patient       =& $consultation->_ref_patient;
$const_med     =  $patient->_ref_constantes_medicales;

$patient->evalAge($consultation->_date);
if (!$exam_possum->age && $patient->_annees != "??") {
  if ($patient->_annees >= 71) {
    $exam_possum->age = "sup71";
  }
  elseif ($patient->_annees >= 61) {
    $exam_possum->age = "61";
  }
  else {
    $exam_possum->age = "inf60";
  }
}
if (!$exam_possum->hb && $dossier_anesth->hb) {
  if ($dossier_anesth->hb <= 9.9) {
    $exam_possum->hb = "inf9.9";
  }
  elseif ($dossier_anesth->hb >= 10 && $dossier_anesth->hb <= 11.4 ) {
    $exam_possum->hb = "10";
  }
  elseif ($dossier_anesth->hb >= 11.5 && $dossier_anesth->hb <= 12.9 ) {
    $exam_possum->hb = "11.5";
  }
  elseif ($dossier_anesth->hb >= 13 && $dossier_anesth->hb <= 16 ) {
    $exam_possum->hb = "13";
  }
  elseif ($dossier_anesth->hb >= 16.1 && $dossier_anesth->hb <= 17 ) {
    $exam_possum->hb = "16.1";
  }
  elseif ($dossier_anesth->hb >= 17.1 && $dossier_anesth->hb <= 18 ) {
    $exam_possum->hb = "17.1";
  }
  elseif ($dossier_anesth->hb >= 18.1) {
    $exam_possum->hb = "sup18.1";
  }
}

if (!$exam_possum->pression_arterielle && $const_med->ta) {
  $tasys = $const_med->_ta_systole * 10;
  if ($tasys <= 89) {
    $exam_possum->pression_arterielle = "inf89";
  }
  elseif ($tasys >= 90  && $tasys<= 99) {
    $exam_possum->pression_arterielle = "90";
  }
  elseif ($tasys >= 100 && $tasys<= 109) {
    $exam_possum->pression_arterielle = "100";
  }
  elseif ($tasys >= 110 && $tasys<= 130) {
    $exam_possum->pression_arterielle = "110";
  }
  elseif ($tasys >= 131 && $tasys<= 170) {
    $exam_possum->pression_arterielle = "131";
  }
  elseif ($tasys >= 171) {
    $exam_possum->pression_arterielle = "sup171";
  }
}
if (!$exam_possum->kaliemie && $dossier_anesth->k) {
  if ($dossier_anesth->k <= 2.8 ) {
    $exam_possum->kaliemie = "inf2.8";
  }
  elseif ($dossier_anesth->k >= 2.9 && $dossier_anesth->k <= 3.1) {
    $exam_possum->kaliemie = "2.9";
  }
  elseif ($dossier_anesth->k >= 3.2 && $dossier_anesth->k <= 3.4) {
    $exam_possum->kaliemie = "3.2";
  }
  elseif ($dossier_anesth->k >= 3.5 && $dossier_anesth->k <= 5.0) {
    $exam_possum->kaliemie = "3.5";
  }
  elseif ($dossier_anesth->k >= 5.1 && $dossier_anesth->k <= 5.3) {
    $exam_possum->kaliemie = "5.1";
  }
  elseif ($dossier_anesth->k >= 5.4 && $dossier_anesth->k <= 5.9) {
    $exam_possum->kaliemie = "5.4";
  }
  elseif ($dossier_anesth->k >= 6.0) {
    $exam_possum->kaliemie = "sup6.0";
  }
}

if (!$exam_possum->natremie && $dossier_anesth->na) {
  if ($dossier_anesth->na <= 125 ) {
    $exam_possum->natremie = "inf125";
  }
  elseif ($dossier_anesth->na >= 126 && $dossier_anesth->na <= 130) {
    $exam_possum->natremie = "126";
  }
  elseif ($dossier_anesth->na >= 131 && $dossier_anesth->na <= 135) {
    $exam_possum->natremie = "131";
  }
  elseif ($dossier_anesth->na >= 136) {
    $exam_possum->natremie = "sup136";
  }
}

$exam_possum->updateFormFields();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("exam_possum" , $exam_possum);

$smarty->display("exam_possum.tpl");
