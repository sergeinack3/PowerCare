<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;

CCanDo::checkEdit();

$chir_id     = CView::get("chir_id", "ref class|CMediusers");
$function_id = CView::get("function_id", "ref class|CFunctions");
$debut_stat  = CView::get("debut_stat", "date", true);
$fin_stat    = CView::get("fin_stat", "date", true);

CView::checkin();
CView::enableSlave();

$chir = new CMediusers();
$chir->load($chir_id);

$function = new CFunctions();
$function->load($function_id);

$protocole = new CProtocole();

$where = array();

if ($chir->_id) {
  $where[] = "protocole.chir_id = '$chir->_id' OR protocole.function_id = '$chir->function_id'";
}
else {
  $where["protocole.function_id"] = "= '$function->_id'";
}

$protocoles = $protocole->loadList($where);

$ds = CSQLDataSource::get("std");

$request = new CRequest(false);
$request->addSelect("protocole_id, COUNT(*) as count");
$request->addTable("sejour");
$request->addLJoin(
  array("operations" => "operations.sejour_id = sejour.sejour_id")
);
$request->addWhere(
  array(
    "protocole_id" => CSQLDataSource::prepareIn(array_keys($protocoles)),
    "DATE(entree) <= '$fin_stat'",
    "DATE(sortie) >= '$debut_stat'"
  )
);
$request->addGroup("protocole_id");
$request->addOrder("COUNT(*) DESC");

$results = $ds->loadList($request->makeSelect());

$count_total = array_sum(CMbArray::pluck($results, "count"));

foreach ($results as $_key => $_result) {
  $results[$_key]["percent"] = round($_result["count"] / $count_total, 2);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("results"    , $results);
$smarty->assign("count_total", $count_total);
$smarty->assign("protocoles" , $protocoles);

$smarty->display("inc_stats_protocoles");