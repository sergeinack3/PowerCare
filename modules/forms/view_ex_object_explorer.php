<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkEdit();
}

$date_min        = CValue::getOrSession("date_min", CMbDT::date("-1 MONTH"));
$date_max        = CValue::getOrSession("date_max", CMbDT::date());
$reference_class = CValue::get("reference_class");
$reference_id    = CValue::get("reference_id");

$reference = null;
if ($reference_class && $reference_id) {
    /** @var CMbObject $reference */
    $reference = new $reference_class();
    $reference->load($reference_id);
}

$groups = CGroups::loadGroups(PERM_READ);

$field = new CExClassField();

$smarty = new CSmartyDP();
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("groups", $groups);
$smarty->assign("field", $field);
$smarty->assign("reference", $reference);
$smarty->display("view_ex_object_explorer.tpl");
