<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CFunctionCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();

$prescription_id = CView::get("prescription_id", "ref class|CPrescription");
$category_id     = CView::get("category_id", "ref class|CCategoryPrescription");
$full_line_id    = CView::get("full_line_id", "ref class|CPrescriptionLineElement");

CView::checkin();

$cat = new CCategoryPrescription();
$cat->load($category_id);

$ljoin = array(
  "element_prescription" => "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id"
);
$where = array(
  "prescription_id"                               => " = '$prescription_id'",
  "element_prescription.category_prescription_id" => " = '$category_id'"
);

$lines = array();
$line  = new CPrescriptionLineElement();

foreach ($line->loadList($where, "debut ASC", null, null, $ljoin) as $_line) {
  /** @var CPrescriptionLineElement $_line */
  $_line->getRecentModification();
  $lines[$category_id][$_line->element_prescription_id][] = $_line;
}

$curr_user             = CMediusers::get();
$can_edit_prescription = $curr_user->isPraticien() || $curr_user->isAdmin();

// Possibilité de stopper les lignes pour les exécutants
$can_stop_lines = array(
  $cat->_id => $can_edit_prescription
);

if ($cat->prescription_executant) {
  $func_cat_presc                           = new CFunctionCategoryPrescription();
  $func_cat_presc->function_id              = $curr_user->function_id;
  $func_cat_presc->category_prescription_id = $cat->_id;

  if ($func_cat_presc->loadMatchingObject()) {
    $can_stop_lines[$func_cat_presc->category_prescription_id] = $curr_user->isExecutantPrescription() && $func_cat_presc->_id;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("full_line_id", $full_line_id);
$smarty->assign("lines", $lines);
$smarty->assign("category_id", $category_id);
$smarty->assign("nodebug", true);
$smarty->assign("can_edit_prescription", $can_edit_prescription);
$smarty->assign("can_stop_lines", $can_stop_lines);
$smarty->display("inc_list_lines");