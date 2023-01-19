<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;

$item_id      = CValue::getOrSession("item_id");
$object_class = CValue::getOrSession("object_class");
$object_id    = CValue::getOrSession("object_id");

$item = new CItemPrestation;
$item->load($item_id);
$item->loadRefsNotes();

if (!$item->_id) {
  $item->object_class = $object_class;
  $item->object_id    = $object_id;
  $item->rank         = 1;

  if ($object_class == "CPrestationJournaliere") {
    /** @var CPrestationJournaliere $prestation */
    $prestation = new $object_class;
    $prestation->load($object_id);
    $item->rank = ($prestation->countBackRefs("items") + 1);
  }
}

$smarty = new CSmartyDP;

$smarty->assign("item", $item);

$smarty->display("inc_edit_item_prestation.tpl");

