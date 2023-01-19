<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassFieldActionButton;

CCanDo::checkEdit();

$ex_action_button_id = CView::get("ex_action_button_id", "ref notNull class|CExClassFieldActionButton");

CView::checkin();

$action_button = new CExClassFieldActionButton();

if ($action_button->load($ex_action_button_id)) {
  $action_button->loadRefsNotes();
}

$ex_class = $action_button->loadRefExGroup()->loadRefExClass();

$action_button->loadRefPredicate()->loadView();

$field = $action_button->loadRefExClassFieldSource();
$field->updateTranslation();

$field = $action_button->loadRefExClassFieldTarget();
$field->updateTranslation();

$triggerable = new CExClass();

$group_id = CGroups::loadCurrent()->_id;

$where = array(
  "group_id = '$group_id' OR group_id IS NULL",
  "conditional"            => "= '1'",
  $triggerable->_spec->key => "!= '$ex_class->_id'",
);

$triggerables_cond = $triggerable->loadList($where, "conditional DESC, name");

$where["conditional"] = "= '0'";
$where["group_id"]    = "= '$group_id'";
$triggerables_others  = $triggerable->loadList($where, "conditional DESC, name");

$smarty = new CSmartyDP();
$smarty->assign("action_button", $action_button);
$smarty->assign("triggerables_cond", $triggerables_cond);
$smarty->assign("triggerables_others", $triggerables_others);
$smarty->display("inc_edit_ex_action_button.tpl");