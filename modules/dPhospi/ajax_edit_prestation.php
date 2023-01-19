<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;

$prestation_id = CValue::getOrSession("prestation_id");
$object_class  = CValue::getOrSession("object_class");
$item_id       = CValue::get("item_id");

$prestation = new $object_class;
$prestation->load($prestation_id);
$prestation->loadRefsNotes();

if (!$prestation->_id) {
  $prestation->group_id = CGroups::loadCurrent()->_id;
}

$smarty = new CSmartyDP;

$smarty->assign("prestation", $prestation);
$smarty->assign("item_id", $item_id);
$smarty->display("inc_edit_prestation.tpl");

