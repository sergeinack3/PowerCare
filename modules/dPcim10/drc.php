<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cim10\Drc\CDRCConsultationResult;
use Ox\Mediboard\Cim10\Drc\CDRCResultClass;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$consult_id = CView::get('consult_id', 'ref class|CConsultation');
$mode       = CView::get('mode', 'enum list|consultation|antecedents|pathologie default|consultation');
$ged        = CView::get('ged', 'bool default|0');

CView::checkin();

$consult = new CConsultation();
$patient = new CPatient();
$dossier = new CDossierMedical();
if ($consult_id) {
  $consult->load($consult_id);

  CAccessMedicalData::logAccess($consult);

  $patient = $consult->loadRefPatient();
  $dossier = $patient->loadRefDossierMedical();
}

/* We list the results under the 'All' class */
$results = CDRCConsultationResult::search('', null, $patient->_annees, null, null, 0, null);

$smarty = new CSmartyDP();
$smarty->assign('result', new CDRCConsultationResult());
$smarty->assign('result_classes', CDRCResultClass::getClasses());
$smarty->assign('patient', $patient);
$smarty->assign('dossier', $dossier);
$smarty->assign('consult', $consult);
$smarty->assign('results', $results);
$smarty->assign('mode', $mode);
$smarty->assign('ged', $ged);
$smarty->display('inc_drc.tpl');
