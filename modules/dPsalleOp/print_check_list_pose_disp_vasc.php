<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;

CCanDo::checkRead();

$pose_disp_vasc_id = CView::get("pose_disp_vasc_id", 'ref class|CPoseDispositifVasculaire');

CView::checkin();

$pose = new CPoseDispositifVasculaire;
$pose->load($pose_disp_vasc_id);
$pose->loadRefSejour();

$check_lists = $pose->loadBackRefs("check_lists", "daily_check_list_id");
foreach ($check_lists as $_check_list_id => $_check_list) {
  // Remove check lists not signed
  if (!$_check_list->validator_id) {
    unset($pose->_back["check_lists"][$_check_list_id]);
    unset($check_lists[$_check_list_id]);
    continue;
  }

  $_check_list->loadItemTypes();
  $_check_list->loadBackRefs('items', "daily_check_item_id");
  foreach ($_check_list->_back['items'] as $_item) {
    $_item->loadRefsFwd();
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("pose", $pose);
$smarty->assign("sejour", $pose->_ref_sejour);
$smarty->assign("patient", $pose->_ref_sejour->loadRefPatient());
$smarty->display("print_check_list_pose_disp_vasc.tpl");
