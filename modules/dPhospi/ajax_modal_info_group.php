<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CInfoGroup;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$info_group_id = CView::get("info_group_id", "ref class|CInfoGroup");
$service_id    = CView::get("service_id", "ref class|CService");
$force_type    = CView::get("force_type", "bool default|0");
CView::checkin();

$info_group = new CInfoGroup();
$info_group->load($info_group_id);
if (!$info_group->_id) {
  $user  = CMediusers::get();
  $group = CGroups::loadCurrent();

  $info_group->user_id  = $user->_id;
  $info_group->group_id = $group->_id;

  if ($service_id) {
    $info_group->actif      = "1";
    $info_group->service_id = $service_id;
  }
}
$info_group->loadRefType();
$info_group->loadRefPatient();

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("info_group", $info_group);
$smarty->assign("force_type", $force_type);

$smarty->display("inc_modal_info_group.tpl");
