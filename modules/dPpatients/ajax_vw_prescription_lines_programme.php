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
use Ox\Mediboard\Patients\CInclusionProgrammeLine;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;

CCanDo::checkRead();
$inclusion_programme_id = CView::get("inclusion_programme_id", "ref class|CInclusionProgramme");
CView::checkin();

$where                           = array();
$where["inclusion_programme_id"] = " = '$inclusion_programme_id'";
$inclusion_programme             = new CInclusionProgrammeLine();
$inclusion_lines                 = $inclusion_programme->loadList($where);

foreach ($inclusion_lines as $_inclusion_line) {
  $prescriptionLine = $_inclusion_line->loadRefObject();
  if ($prescriptionLine instanceof CPrescriptionLineMix) {
    $prescriptionLine->loadRefsLines();
  }
  else {
    $prescriptionLine->loadRefsPrises();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("inclusion_lines", $inclusion_lines);
$smarty->display("inc_vw_prescription_lines_programme.tpl");
