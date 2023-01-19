<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CSousItemPrestation;

$sous_item_id       = CValue::get("sous_item_id");
$item_prestation_id = CValue::get("item_prestation_id");

$sous_item = new CSousItemPrestation();
$sous_item->load($sous_item_id);

if (!$sous_item->_id) {
  $sous_item->item_prestation_id = $item_prestation_id;
}

$sous_item->loadRefItemPrestation();

$smarty = new CSmartyDP();

$smarty->assign("sous_item", $sous_item);

$smarty->display("inc_edit_sous_item");