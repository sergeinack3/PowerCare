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

CCanDo::checkAdmin();

$step             = CView::get("step", 'num default|100', true);
$start            = CView::get("start", 'num default|0', true);
$directory        = CView::get("directory", "str", true);
$files_directory  = CView::get("files_directory", "str", true);
$update_data      = CView::get("update_data", "str", true);
$patient_id       = CView::get("patient_id", "ref class|CPatient", true);
$link_files_to_op = CView::get("link_files_to_op", "str", true);
$correct_files    = CView::get("correct_files", "str", true);
$handlers         = CView::get("handlers", "str", true);
$patients_only    = CView::get("patients_only", "str", true);
$date_min         = CView::get("date_min", "date", true);
$date_max         = CView::get("date_max", "date", true);

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("step", $step);
$smarty->assign("start", $start);
$smarty->assign("directory", $directory);
$smarty->assign("files_directory", $files_directory);
$smarty->assign("update_data", $update_data);
$smarty->assign("patient_id", $patient_id);
$smarty->assign("link_files_to_op", $link_files_to_op);
$smarty->assign("correct_files", $correct_files);
$smarty->assign("handlers", $handlers);
$smarty->assign("patients_only", $patients_only);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->display("vw_import_patients.tpl");
