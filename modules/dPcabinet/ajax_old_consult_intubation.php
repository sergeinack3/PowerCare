<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CValue::get("patient_id");
$consult_anesth_id = CValue::get("consult_anesth_id");
$patient = new CPatient();
$patient->load($patient_id);

$dossiers_anesth = array();

foreach ($patient->loadRefsConsultations() as $_consult) {
  foreach ($_consult->loadRefsDossiersAnesth() as $_dossier) {
    if ($_dossier->_id != $consult_anesth_id) {
      $_dossier->loadRefsRisques();
      $_dossier->_ref_consultation = $_consult;
      $_consult->loadRefPraticien()->loadRefFunction();
      $_consult->loadRefPlageConsult(true);
      $dossiers_anesth[] = $_dossier;
    }
  }
}

$smarty = new CSmartyDP;
$smarty->assign("dossiers_anesth", $dossiers_anesth);
$smarty->assign("patient", $patient);
$smarty->assign("moebius_active", CModule::getActive("moebius"));
$smarty->display("inc_consult_anesth/vw_old_consult_anesth.tpl");
