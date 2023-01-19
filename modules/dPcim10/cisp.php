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
use Ox\Mediboard\Patients\CDossierMedical;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");
$consult_id = CView::get("consult_id", "ref class|CConsultation");
$mode       = CView::get('mode', 'enum list|consultation|antecedents|pathologie default|consultation');

CView::checkin();

CAccessMedicalData::logAccess("CConsultation-$consult_id");

$dossier_medical = new CDossierMedical();
$dossier_medical->object_class = "CPatient";
$dossier_medical->object_id = $patient_id;
$dossier_medical->loadMatchingObject();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("dossier_medical", $dossier_medical);
$smarty->assign("consult_id", $consult_id);
$smarty->assign('mode', $mode);
$smarty->display("cisp");