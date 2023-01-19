<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CInclusionProgramme;

$programme_id = CView::get("programme_id", "ref class|CProgrammeClinique");
CView::checkin();

$where                          = array();
$where["programme_clinique_id"] = " = '$programme_id'";

$include_programme  = new CInclusionProgramme();
$includes_programme = $include_programme->loadList($where);

foreach ($includes_programme as $_include_programme) {
  $_include_programme->loadRefPatient();
}

$smarty = new CSmartyDP();
$smarty->assign("includes_programme", $includes_programme);
$smarty->display("vw_patient_programme.tpl");
