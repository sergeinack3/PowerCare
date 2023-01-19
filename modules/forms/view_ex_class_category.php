<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassCategory;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkEdit();
}

$category   = new CExClassCategory();
$categories = $category->loadGroupList(null, "title");

$smarty = new CSmartyDP();
$smarty->assign("categories", $categories);
$smarty->display("view_ex_class_category.tpl");
