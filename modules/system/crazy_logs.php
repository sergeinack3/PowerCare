<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CCanDo::checkAdmin();

$mode  = CView::get('mode', 'str default|find');
$ratio = CView::get('ratio', 'num default|300');
$limit = CView::get('limit', 'num default|1000');
$class = CView::get('class', 'str');
CView::checkin();

if ($mode != "purge") {
  CView::enforceSlave();
}

/** @var CStoredObject $log */
$log = new $class();
$ds  = $log->getDS();
$table = $log->_spec->table;
$key = $log->_spec->key;
(strpos($class, 'Access') !== false) ? $param = "hits" : $param = "requests";

// Purge
$purged_count = null;
if ($mode == "purge") {
  $request = new CRequest();
  $request->addSelect($key);
  $request->addTable($table);
  $request->addWhere("`duration` / `$param` > '$ratio'");
  $request->setLimit($limit);

  $result = $ds->loadList($request->makeSelect());
  $ids    = CMbArray::pluck($result, $key);

  if (!empty($ids)) {
    $ids = implode(", ", $ids);

    $request = new CRequest();
    $request->addTable($table);
    $request->addWhere("`$key` IN ($ids)");
    $request->setLimit($limit);

    $ds->exec($request->makeDelete());
    $purged_count = $ds->affectedRows();
  }
}

// Détection
$request = new CRequest();
$request->addSelect('`module_action`.module AS _module');
$request->addSelect('`module_action`.action AS _action');
$request->addSelect('COUNT(*) AS total');
$request->addTable($table);
$request->addTable('module_action');
$request->addWhere("duration / $param > '$ratio'");
$request->addWhere("`module_action`.`module_action_id` = `$table`.`module_action_id`");
$request->addGroup('`module_action`.module, `module_action`.action');

$logs = $ds->loadList($request->makeSelect());

$smarty = new CSmartyDP();
$smarty->assign("logs", $logs);
$smarty->assign("ratio", $ratio);
$smarty->assign("purged_count", $purged_count);
$smarty->assign("class", $class);
$smarty->display("crazy_logs.tpl");