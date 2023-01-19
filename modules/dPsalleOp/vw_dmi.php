<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;

$operation_id = CView::get("operation_id", 'ref class|COperation', true);

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$url_application = CAppUI::gconf("vivalto general url_application");
$url_application .= "?interventionMB=$operation_id";

$smarty = new CSmartyDP;

$smarty->assign("url_application", $url_application);

$smarty->display("vw_dmi");
