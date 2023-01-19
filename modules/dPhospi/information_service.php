<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CInfoGroup;

CCanDo::checkRead();

$date          = CView::get("date", "date default|now");
$service_id    = CView::get("service_id", "ref class|CService");
$show_inactive = CView::get("show_inactive", "bool default|0", true);
CView::checkin();

$date_debut = CMbDT::dateTime("-1 day", $date);
$date_fin   = CMbDT::dateTime("+1 day", $date);

$group = CGroups::get();

$info_service = new CInfoGroup();
$where        = array(
  "service_id" => " = '$service_id'",
  "date"       => "BETWEEN '$date_debut' AND '$date_fin'",
  "group_id"   => "= '$group->_id'"
);
if (!$show_inactive) {
  $where["actif"] = " = '1'";
}
$order         = "date DESC";
$infos_service = $info_service->loadList($where, $order);

CStoredObject::massLoadFwdRef($infos_service, "patient_id");
CStoredObject::massLoadFwdRef($infos_service, "user_id");
foreach ($infos_service as $_info_service) {
  $_info_service->loadRefPatient();
  $_info_service->loadRefUser();
}

$where["actif"] = " = '0'";
$count_inactive = $info_service->countList($where);

$smarty = new CSmartyDP();
$smarty->assign("infos_service", $infos_service);
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("count_inactive", $count_inactive);
$smarty->display("information_service");
