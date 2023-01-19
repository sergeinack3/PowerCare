<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Stock\CProductMovement;
use Ox\Mediboard\Stock\CProductStockGroup;

CCanDo::checkEdit();

$datetime_min = CView::get('_datetime_min', 'dateTime', true);
$datetime_max = CView::get('_datetime_max', 'dateTime', true);
$account      = CView::get('account', 'str', true);
$origin_class = CView::get('origin_class', 'str', true);

$export = CView::get('export', 'bool');

CView::checkin();

$group_id = CProductStockGroup::getHostGroup(true);

$where = array(
  "product_cump.group_id" => "= '$group_id'",
);

$ljoin = array(
  "product_cump" => "product_cump.product_cump_id = product_movement.cump_id"
);

$movement = new CProductMovement();
$ds       = $movement->getDS();

if ($datetime_min) {
  $where[] = $ds->prepare("product_movement.datetime > ?", $datetime_min);
}
if ($datetime_max) {
  $where[] = $ds->prepare("product_movement.datetime < ?", $datetime_max);
}
if ($account) {
  $where["product_movement.account"] = $ds->prepareLike("$account%");
}
if ($origin_class) {
  $where["product_movement.origin_class"] = $ds->prepare("= ?", $origin_class);
}

$movements = $movement->loadList($where, "datetime DESC, origin_class, origin_id", null, null, $ljoin);

$origins = CStoredObject::massLoadFwdRef($movements, "origin_id");
$objects = CStoredObject::massLoadFwdRef($movements, "object_id");

if ($export) {
  $csv = new CCSVFile();

  $columns = array(
    "date",
    "compte",
    "montant",
    "destination",
  );

  $csv->writeLine($columns);

  foreach ($movements as $_movement) {
    $row = array(
      $_movement->datetime,
      $_movement->account,
      $_movement->amount,
      $_movement->loadTargetObject()->_view,
    );

    $csv->writeLine($row);
  }

  $csv->stream("Export mouvements");

  CApp::rip();
}
else {
  foreach ($origins as $_origin) {
    $_origin->loadView();
  }

  // Smarty template
  $smarty = new CSmartyDP();

  $smarty->assign('movements', $movements);

  $smarty->display('inc_list_movements.tpl');
}

