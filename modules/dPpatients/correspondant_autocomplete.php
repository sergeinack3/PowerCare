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
use Ox\Mediboard\Patients\CCorrespondantPatient;

CCanDo::checkRead();

$name        = CView::get("nom", "str");
$function_id = CView::get("function_id", "ref class|CFunctions");
$group_id    = CView::get("group_id", "ref class|CGroups");
$relation    = CView::get("relation", "str");

CView::checkin();

CView::enableSlave();

$correspondant = new CCorrespondantPatient();
$ds            = $correspondant->getDS();
$where         = [];

if ($function_id) {
  $where[] = $ds->prepare("function_id IS NULL OR function_id = ?", $function_id);
}
elseif ($group_id) {
  $where[] = $ds->prepare("group_id IS NULL OR group_id = ?", $group_id);
}

$where[] = "patient_id IS NULL";
$where[] = $ds->prepare("relation = ?", $relation);

$matches = $correspondant->getAutocompleteList($name, $where);
foreach ($matches as $_match) {
  $_match->updateFormFields();
}

$template = $correspondant->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', true);
$smarty->assign('field', '_longview');
$smarty->assign('show_view', false);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);

$smarty->display("inc_field_autocomplete");
