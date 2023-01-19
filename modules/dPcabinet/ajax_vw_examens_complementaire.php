<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CExamComp;

$dossier_anesth_id = CValue::get("dossier_anesth_id");

$dossier_anesth = new CConsultAnesth();
$dossier_anesth->load($dossier_anesth_id);

$consult = $dossier_anesth->loadRefConsultation();
$patient = $consult->loadRefPatient();
$patient->loadRefDossierMedical();

$smarty = new CSmartyDP();

$smarty->assign("consult_anesth"  , $dossier_anesth);
$smarty->assign("mins"    , range(0, 15-1, 1));
$smarty->assign("secs"    , range(0, 60-1, 1));
$smarty->assign("consult" , $consult);
$smarty->assign("patient" , $patient);
$smarty->assign("examComp", new CExamComp());
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("view_prescription", 0);

$smarty->display("inc_consult_anesth/acc_examens_complementaire.tpl");
