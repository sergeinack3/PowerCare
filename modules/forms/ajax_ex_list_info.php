<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExList;

$list_id = CValue::get("list_id");

$list = new CExList();
$list->load($list_id);
$list->loadRefItems();

$smarty = new CSmartyDP();
$smarty->assign("list", $list);
$smarty->display("inc_ex_list_info.tpl");