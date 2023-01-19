<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CInclusionProgramme;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CProgrammeClinique;

$inclusion_programme_id = CView::get("inclusion_programme_id", "ref class|CInclusionProgramme");
$patient_id             = CView::get("patient_id", "ref class|CPatient", true);
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$where = array("programme_clinique.annule" => "= '0'",
               "users_mediboard.function_id" => "= '".CMediusers::get()->function_id."'");

$join = array("users_mediboard" => "users_mediboard.user_id = programme_clinique.coordinateur_id");

$programme_clinique = new CProgrammeClinique();
$programmes         = $programme_clinique->loadList($where, null, null, null , $join);

$inclusion_programme = new CInclusionProgramme();
$inclusion_programme->load($inclusion_programme_id);

$inclusion_programme->loadRefProgrammeClinique();

$smarty = new CSmartyDP();
$smarty->assign("inclusion_programme", $inclusion_programme);
$smarty->assign("patient", $patient);
$smarty->assign("programmes", $programmes);

$smarty->display("vw_edit_inclusion_programme.tpl");
