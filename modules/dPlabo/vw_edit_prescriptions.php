<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CValue::getOrSession("patient_id");
$typeListe  = CValue::getOrSession("typeListe");

// Permettre de le remettre à null lors d'un changement de patient
CValue::getOrSession("prescription_labo_id");

// Chargement du patient
$patient = new CPatient;
$patient->load($patient_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient"  , $patient);
$smarty->assign("typeListe", $typeListe);

$smarty->display("vw_edit_prescriptions");
