<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$pat_id = CValue::getOrSession("pat_id");

// Chargement du dossier patient
$patient = new CPatient;
$patient->load($pat_id);

if ($patient->patient_id) {
  $patient->loadIPP();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient"         , $patient );
$smarty->assign("hprim21installed", CModule::getActive("hprim21"));

$smarty->display("inc_ipp_form");
