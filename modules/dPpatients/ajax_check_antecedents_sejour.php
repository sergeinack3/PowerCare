<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CAntecedent;

CCanDo::checkEdit();

$antecedent_id = CView::get('antecedent_id', 'ref class|CAntecedent');
$sejour_id     = CView::get('sejour_id', 'ref class|CSejour');

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

/** @var CAntecedent $antecedent */
$antecedent = CMbObject::loadFromGuid("CAntecedent-$antecedent_id");

$antecedent->loadRefDossierMedical();

$antecedent->loadRefLinkedElements($sejour_id);

$smarty = new CSmartyDP();
$smarty->assign('antecedent', $antecedent);
$smarty->assign('dossier_medical', $antecedent->_ref_dossier_medical);
$smarty->assign('dossier_medical_sejour', $antecedent->_dossier_medical_sejour);
$smarty->display('inc_check_antecedents_sejour.tpl');
