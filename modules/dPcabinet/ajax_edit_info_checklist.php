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
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkEdit();
$info_checklist_id = CView::get("info_checklist_id", "ref class|CInfoChecklist");
CView::checkin();

$group = CGroups::loadCurrent();

$info = new CInfoChecklist();
$info->load($info_checklist_id);
$info->group_id = $group->_id;

// Fonctions
$functions = $group->loadFunctions();

$smarty = new CSmartyDP();

$smarty->assign("info"     , $info);
$smarty->assign("functions", $functions);

$smarty->display("vw_edit_info_checklist.tpl");
