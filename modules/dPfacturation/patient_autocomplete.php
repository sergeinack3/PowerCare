<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_keywords = CView::get("_seek_patient", "str");
CView::checkin();

CView::enableSlave();

$where = [];

if (CAppUI::isCabinet() || CAppUI::isGroup()) {
  $functions = (CGroups::loadCurrent())->loadFunctions();
  $ds        = CSQLDataSource::get('std');
  $where[]   = "function_id " . CSQLDataSource::prepareIn(CMbArray::pluck($functions, "function_id")) . " OR function_id IS NULL";
}

$patient = new CPatient();

$matches = $patient->getAutocompleteList($patient_keywords, $where, 30);
foreach ($matches as $_match) {
  $_match->updateFormFields();
}

$template = $patient->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', true);
$smarty->assign('field', 'patient_id');
$smarty->assign('show_view', false);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);

$smarty->display("inc_field_autocomplete");
