<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::request("patient_id", "num");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$dossier_medical = $patient->loadRefDossierMedical();
$dossier_medical->loadRefsAntecedents();

$smarty = new CSmartyDP();

$smarty->assign("dossier_medical", $dossier_medical);

$smarty->display("inc_atcd_majeur.tpl");