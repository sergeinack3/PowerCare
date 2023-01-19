<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;

$dossier_anesth_id = CValue::get("dossier_anesth_id");
$sejour_id         = CValue::get("sejour_id");

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$dossier_anesth = new CConsultAnesth();
$dossier_anesth->load($dossier_anesth_id);

$consult = $dossier_anesth->loadRefConsultation();
$consult->loadRefPlageConsult();
$patient = $consult->loadRefPatient();
$patient->loadRefDossierMedical();

$smarty = new CSmartyDP();

$smarty->assign("dossier_anesth_id", $dossier_anesth_id);
$smarty->assign("patient"       , $patient);
$smarty->assign("sejour_id"     , $sejour_id);
$smarty->assign("_is_anesth"    , $consult->_is_anesth);

$smarty->display("inc_vw_list_antecedents.tpl");