<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();
$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$hide_old_lines = CView::get("hide_old_lines", "bool default|1");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$cibles = $last_trans_cible = $users = $functions = array();
$sejour->loadSuiviMedical(null, null, $cibles, $last_trans_cible, null, $users, null, $functions, 0, null, 1, "dietetique");
$prescription = $sejour->loadRefPrescriptionSejour();
$prescription->loadRefsLinesElement();

$hidden_lines_count = 0;
$date               = CMbDT::date();

CPrescription::massCountPlanifications($prescription);
foreach ($prescription->_ref_prescription_lines_element as $_line_elt) {
  $line_diet    = $_line_elt->_ref_element_prescription->prescriptible_dieteticien || $_line_elt->dietetique;
  $line_to_hide = $line_diet && $_line_elt->_fin_reelle && CMbDT::date($_line_elt->_fin_reelle) < $date && !$_line_elt->suspendu;
  if ($line_to_hide) {
    $hidden_lines_count++;
  }
  if (!$line_diet || ($line_to_hide && $hide_old_lines)) {
    unset($prescription->_ref_prescription_lines_element[$_line_elt->_id]);
    continue;
  }
  $_line_elt->loadRefParentLine();
}

$smarty = new CSmartyDP();
$smarty->assign("prescription", $prescription);
$smarty->assign("sejour", $sejour);
$smarty->assign("last_trans_cible", $last_trans_cible);
$smarty->assign("cibles", $cibles);
$smarty->assign("users", $users);
$smarty->assign("hide_old_lines", $hide_old_lines);
$smarty->assign("hidden_lines_count", $hidden_lines_count);
$smarty->display("vw_dietetique");
