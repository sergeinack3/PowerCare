<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CInfoChecklist;

CCanDo::checkEdit();
$hide_inactif = CView::get("hide_inactif", "bool default|1", true);
$only_list    = CView::get("only_list", "bool default|0");
CView::checkin();

$where = array();
if ($hide_inactif) {
  $where["actif"] = " = '1'";
}

$info = new CInfoChecklist();
$infos = $info->loadGroupList($where, "libelle");

$smarty = new CSmartyDP();

$smarty->assign("infos"       , $infos);
$smarty->assign("hide_inactif", $hide_inactif);

if ($only_list) {
  $smarty->display("vw_list_info_checklist.tpl");
}
else {
  $smarty->display("vw_info_checklist.tpl");
}
