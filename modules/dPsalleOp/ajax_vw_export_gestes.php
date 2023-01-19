<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\SalleOp\CGestePeropXMLExport;

CCanDo::checkEdit();
$current_group = CView::get("current_group", "bool default|0");
$function_id   = CView::get("function_id", "ref class|CFunctions");
$user_id       = CView::get("user_id", "ref class|CMediusers");
CView::checkin();

$counter = 0;
$where   = array();

if ($current_group) {
  $group = CGroups::loadCurrent();
  $where["group_id"] = " = '$group->_id'";
}
elseif ($function_id) {
  $where["function_id"] = " = '$function_id'";
}
elseif ($user_id) {
  $where["user_id"] = " = '$user_id'";
}

CGestePeropXMLExport::export($where);

CApp::rip();
