<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CItemPrestation;

CCanDo::check();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = "$object_class-$object_id";

$prestation = CMbObject::loadFromGuid($object_guid);

if (!$prestation || !$prestation->_id) {
    CAppUI::notFound($object_guid);
}

$item_id = CView::get("item_id", "ref class|CItemPrestation");

CView::checkin();

$items = $prestation->loadBackRefs("items", "rank");

CStoredObject::massLoadBackRefs($items, "sous_items");

foreach ($items as $_item) {
    $_item->loadRefsSousItems();
}

$item = new CItemPrestation();
$item->load($item_id);

$smarty = new CSmartyDP();

$smarty->assign("item", $item);
$smarty->assign("items", $items);
$smarty->assign("item_id", $item_id);
$smarty->assign("prestation", $prestation);

$smarty->display("inc_list_items_prestation.tpl");
