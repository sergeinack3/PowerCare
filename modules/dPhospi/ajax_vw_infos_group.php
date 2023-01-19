<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CInfoGroup;
use Ox\Mediboard\Mediusers\CMediusers;

$show_inactive = CView::get("show_inactive", "bool default|0");

CView::checkin();

$user       = CMediusers::get();
$info_group = new CInfoGroup();

$list_infos_group = CInfoGroup::loadFor($user, $show_inactive);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("list_infos_group", $list_infos_group);
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("count_inactive", $info_group->countInactiveItems());

$smarty->display("inc_vw_infos_group.tpl");
