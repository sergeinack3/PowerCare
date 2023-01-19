<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$fhir_resource_id = CView::get("fhir_resource_id", "str");
$base64           = CView::get($fhir_resource_id, "str", true);
$file_type        = CView::get($fhir_resource_id."_file_type", "str", true);
CView::checkin();

if (!$fhir_resource_id || !$base64) {
  CAppUI::stepAjax("common-error-An error occurred", UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("base64"   , $base64);
$smarty->assign("file_type", $file_type);
$smarty->display("inc_show_document.tpl");