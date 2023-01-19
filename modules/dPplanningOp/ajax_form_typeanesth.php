<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkEdit();
$type_anesth_id = CView::get("type_anesth", "ref class|CTypeAnesth");
CView::checkin();

$type_anesth = new CTypeAnesth();
$type_anesth->load($type_anesth_id);

if (!$type_anesth->_id) {
  $type_anesth->group_id = CGroups::loadCurrent()->_id;
}

// Récupération des groups
$groups = CGroups::loadGroups(PERM_EDIT);

//smarty
$smarty = new CSmartyDP();
$smarty->assign("type_anesth", $type_anesth);
$smarty->assign("groups"     , $groups);
$smarty->display("vw_form_typeanesth");