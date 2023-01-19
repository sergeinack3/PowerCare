<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CLitLiaisonItem;

$lit_id = CView::get("lit_id", "ref class|CLit");

CView::checkin();

$lit_liaison_item = new CLitLiaisonItem();

$lit_liaison_item->lit_id = $lit_id;

/** @var CLitLiaisonItem[] $lits_liaisons_items */
$lits_liaisons_items = $lit_liaison_item->loadMatchingList();

$liaisons = CStoredObject::massLoadFwdRef($lits_liaisons_items, "item_prestation_id");

CStoredObject::massLoadFwdRef($liaisons, "object_id");

foreach ($lits_liaisons_items as $_lit_liaison_item) {
  $_lit_liaison_item->loadRefItemPrestation();
  $_lit_liaison_item->_ref_item_prestation->loadRefObject();
}

$smarty = new CSmartyDP();

$smarty->assign("lits_liaisons_items", $lits_liaisons_items);
$smarty->assign("lit_id", $lit_id);

$smarty->display("inc_edit_liaisons_items.tpl");
