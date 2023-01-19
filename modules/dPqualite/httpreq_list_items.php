<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Qualite\CEiItem;

$ei_categorie_id = CValue::get("categorie_id");

$items = array();

if ($ei_categorie_id) {
  $where                    = array();
  $where["ei_categorie_id"] = " = '$ei_categorie_id'";

  $item  = new CEiItem;
  $items = $item->loadList($where);
}

$smarty = new CSmartyDP();
$smarty->assign("items", $items);
$smarty->display("ajax_list_items.tpl");
