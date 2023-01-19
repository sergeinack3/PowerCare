<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

// Filter
$filter = new CPlageconsult();
$filter->_user_id           = CValue::get("_user_id", null);
$filter->_date_min          = CValue::get("_date_min", CMbDT::date("last month"));
$filter->_date_max          = CValue::get("_date_max", CMbDT::date());
$demande_nominativement     = CValue::get("demande_nominativement");

CView::enforceSlave();

// Current user and current function
$mediuser = CMediusers::get();
$function = $mediuser->loadRefFunction();

$ds = $filter->getDS();

$stats_creation = array();
$prats_creation = array();

if ($filter->_user_id) {
  $query = "SELECT
      consultation.owner_id AS user_id,
      COUNT(*) AS total
    FROM consultation
    LEFT JOIN plageconsult ON plageconsult.plageconsult_id = consultation.plageconsult_id
    AND consultation.creation_date BETWEEN '$filter->_date_min 00:00:00' AND '$filter->_date_max 23:59:59'
    AND plageconsult.chir_id = '$filter->_user_id'
    AND consultation.demande_nominativement = '$demande_nominativement'
    GROUP BY consultation.owner_id
    HAVING consultation.owner_id IS NOT NULL";

  $stats_creation = $ds->loadList($query);

  usort(
    $stats_creation,
    function ($a, $b) {
      return $b["total"] - $a["total"];
    }
  );
  
  $where = array();
  $where["user_id"] = CSQLDataSource::prepareIn(CMbArray::pluck($stats_creation, "user_id"));

  /* @var CMediusers[] $prats_creation*/
  $prats_creation = $mediuser->loadList($where);
  CStoredObject::massLoadFwdRef($prats_creation, "function_id");
  
  foreach ($prats_creation as $_prat) {
    $_prat->loadRefFunction();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("filter"        , $filter);
$smarty->assign("prats_creation", $prats_creation);
$smarty->assign("stats_creation", $stats_creation);

$smarty->display("inc_stats_prise_rdv.tpl");
