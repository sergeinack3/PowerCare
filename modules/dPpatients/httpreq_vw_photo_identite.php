<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CView::get('patient_id', 'ref class|CPatient');
$mode       = CView::get('mode', 'str default|read');
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->updateFormFields();
$patient->loadRefPhotoIdentite();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('patient', $patient);
$smarty->assign('mode', $mode);
$smarty->assign('size', 60);
$smarty->display("inc_vw_photo_identite.tpl");
