<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;

CCanDo::checkAdmin();

$dsn       = CView::get("dsn", "str");
$order_col = CView::get("order_col", "str default|count");
$order_way = CView::get("order_way", "enum list|ASC|DESC default|DESC");

CView::setSession("dsn", $dsn);

CView::checkin();

$order_col = ($order_col) ?: 'count';
$order_way = ($order_way) ?: 'DESC';

try {
  $db_info = CImportTools::getDatabaseStructure($dsn, true);
}
catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}

if (!$db_info['tables']) {
  CAppUI::stepAjax('mod-importTools-dsn-empty-or-null', UI_MSG_ERROR);
}

if ($order_col == 'count') {
  uasort(
    $db_info['tables'],
    function ($a, $b) use ($order_way) {
      return ($order_way == 'ASC') ? $a['count'] - $b['count'] : $b['count'] - $a['count'];
    }
  );
}
if ($order_col == 'size') {
  uasort(
    $db_info['tables'],
    function ($a, $b) use ($order_way) {
      return ($order_way == 'ASC') ? $a['size'] - $b['size'] : $b['size'] - $a['size'];
    }
  );
}

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("db_info", $db_info);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->display("inc_vw_tables.tpl");