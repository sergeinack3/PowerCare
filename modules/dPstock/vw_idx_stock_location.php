<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Stock\CProductStockLocation;

CCanDo::checkEdit();

$stock_location = new CProductStockLocation();

$group_id = CGroups::loadCurrent()->_id;
$total       = 0;
$classes  = array(
  "CGroups"         => "Pharmacie",
  "CService"        => CAppUI::tr("CService"),
  "CBlocOperatoire" => CAppUI::tr("CBlocOperatoire"),
);

$ds = $stock_location->getDS();

$lists = array();

foreach ($classes as $_class => $_label) {
  $where = array(
    "product_stock_location.object_class" => $ds->prepare("=?", $_class),
  );
  $ljoin = array();

  switch ($_class) {
    case "CGroups":
      $where["product_stock_location.object_id"] = $ds->prepare("=?", $group_id);
      break;

    case "CService":
      $ljoin["service"]          = "service.service_id = product_stock_location.object_id";
      $where["service.group_id"] = $ds->prepare("=?", $group_id);
      break;

    case "CBlocOperatoire":
      $ljoin["bloc_operatoire"]          = "bloc_operatoire.bloc_operatoire_id = product_stock_location.object_id";
      $where["bloc_operatoire.group_id"] = $ds->prepare("=?", $group_id);
      break;

    default:
      //
  }

  $order = 'object_class, object_id, position, product_stock_location.name';

  $lists[$_class] = $stock_location->loadList($where, $order, null, null, $ljoin);
  $total += count($lists[$_class]);
}


// Création du template
$smarty = new CSmartyDP();
$smarty->assign('lists', $lists);
$smarty->assign('isEmpty', $total === 0);
$smarty->assign('classes', $classes);
$smarty->display('vw_idx_stock_location.tpl');

