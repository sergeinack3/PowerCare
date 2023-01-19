<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassHostField;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkRead();

$ex_class_id  = CValue::get("ex_class_id");
$ex_object_id = CValue::get("ex_object_id");

if (!$ex_class_id) {
  $msg = "Impossible d'afficher le formulaire sans connaître la classe de base";
  CAppUI::stepAjax($msg, UI_MSG_WARNING);
  trigger_error($msg, E_USER_ERROR);

  return;
}

$ex_object = new CExObject($ex_class_id);
$ex_object->load($ex_object_id);

$ex_groups = $ex_object->loadRefExClass()->loadRefsGroups();
foreach ($ex_groups as $_group) {
  $_group->getRankedItems();

  foreach ($_group->_ranked_items as $_ranked_item) {
    if (!$_ranked_item instanceof CExClassHostField) {
      continue;
    }

    $_ranked_item->getHostObject($ex_object);
  }
}

CExObject::checkLocales();

// Création du template
$smarty = new CSmartyDP("modules/forms");
$smarty->assign("ex_object", $ex_object);
$smarty->display("view_ex_object.tpl");
