<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;

CCanDo::checkRead();
$atcd_rques = CView::get("rques", "str");
$patient_id = CView::get("patient_id", "ref class|CPatient");
CView::checkin();

$dossier_medical_id = CDossierMedical::dossierMedicalId($patient_id, "CPatient");

$antecedent = new CAntecedent();
$antecedent->rques = stripslashes(utf8_decode($atcd_rques));
$antecedent->dossier_medical_id = $dossier_medical_id;
$antecedent->loadMatchingObjectEsc();

$atcd = [$dossier_medical_id ? $antecedent->_id : 0];

CApp::json($atcd);
