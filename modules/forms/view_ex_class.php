<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassCategory;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$group_id = CGroups::loadCurrent()->_id;

$where = [
    "group_id = '$group_id' OR group_id IS NULL",
];

if (CExClass::inHermeticMode(true)) {
    $where = [
        "group_id = '$group_id'",
    ];
}

$ex_class = new CExClass();

/** @var CExClass[] $ex_classes */
$ex_classes = $ex_class->loadList($where, "name");

$categories = CStoredObject::massLoadFwdRef($ex_classes, "category_id");
$categories = CStoredObject::naturalSort($categories, ["title"]);

$categories = [new CExClassCategory()] + $categories;

foreach ($ex_classes as $_ex_class) {
    $_category_id                                                = $_ex_class->category_id ?: 0;
    $categories[$_category_id]->_ref_ex_classes[$_ex_class->_id] = $_ex_class;
}

CExObject::checkLocales();

$smarty = new CSmartyDP("modules/forms");
$smarty->assign("object_class", "CExClass");
$smarty->assign("hide_tree", false);
$smarty->assign("categories", $categories);
$smarty->display("view_ex_class.tpl");
