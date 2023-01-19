<?php
/**
 * @package Mediboard\Cabinet
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
use Ox\Mediboard\Cabinet\CAccidentTravail;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$consult_id          = CView::getRefCheckEdit('consult_id', 'ref class|CConsultation');
$sejour_id           = CView::get('sejour_id', 'ref class|CSejour');
$object_class        = CView::get('object_class', 'enum list|CConsultation|CSejour');
$show_button_cerfa   = CView::get('show_button_cerfa', 'bool default|1');
$accident_travail_id = CView::get('accident_travail_id', 'ref class|CAccidentTravail');
CView::checkin();

$sejour = CSejour::findOrNew($sejour_id);

CAccessMedicalData::logAccess($sejour);

$new_view_at = CAppUI::gconf("dPcabinet CAccidentTravail show_new_view_at");

$consultation = new CConsultation();
$consultation->load($consult_id);

CAccessMedicalData::logAccess($consultation);

$date          = CMbDT::date();
$accident_travail = new CAccidentTravail();
$accident_travail->load($accident_travail_id);

if (!$accident_travail->_id) {
  $accident_travail->date_constatations = $consultation->date_at ?: CMbDT::date();
  $accident_travail->date_debut_arret   = $consultation->date_at ?: CMbDT::date();
  $accident_travail->object_id          = $consult_id ?: $sejour_id;
  $accident_travail->object_class       = $object_class;
}

if ($new_view_at) {
  /** @var CConsultation|CSejour $object */
  $object    = $consultation->_id ? $consultation : $sejour;
  $patient   = $object->loadRefPatient();
  $praticien = $object->loadRefPraticien();

// bénéficiaire
  $beneficiary['nir_num']      = $patient->matricule ? substr($patient->matricule, 0, -2) : false;
  $beneficiary['nir_key']      = $patient->matricule ? substr($patient->matricule, -2) : false;
  $beneficiary['usual_name']   = $patient->nom ?: false;
  $beneficiary['last_name']    = $patient->nom_jeune_fille ?: false;
  $beneficiary['first_name']   = $patient->prenom ?: false;
  $beneficiary['birth']        = $patient->naissance ?: false;
  $beneficiary['rank']         = $patient->rang_naissance ?: false;
  $beneficiary['regime']       = $patient->code_regime ?: false;
  $beneficiary['organism']     = $patient->caisse_gest ?: false;
  $beneficiary['gestion']      = $patient->code_gestion ?: false;
  $beneficiary['quality_code'] = $patient->qual_beneficiaire ?: false;
  $beneficiary['quality_text'] = $patient->qual_beneficiaire ? CAppUI::tr('CPatient.qual_beneficiaire.' . $patient->qual_beneficiaire) : false;

// praticien
  $physician['rpps']       = $praticien->rpps ?: false;
  $physician['first_name'] = $praticien->_user_first_name ?: false;
  $physician['last_name']  = $praticien->_user_last_name ?: false;
  $physician['psam']       = $praticien->adeli ?: false;
}

$view = "inc_edit_accident_travail";

$smarty = new CSmartyDP();
$smarty->assign('accident_travail' , $accident_travail);
$smarty->assign('show_button_cerfa', $show_button_cerfa);

if ($new_view_at) {
  $smarty->assign('beneficiary', $beneficiary);
  $smarty->assign('physician'  , $physician);
  $view = "at/inc_create_at";
}

$smarty->display($view);
