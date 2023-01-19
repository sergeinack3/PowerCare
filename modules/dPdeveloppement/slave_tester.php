<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkRead();

/** @var int $times */
$times    = CView::get("times"    , "num notNull pos max|100 default|20");
/** @var int $duration */
$duration = CView::get("duration" , "num notNull pos max|60 default|1");
/** @var bool $do */
$do       = CView::get("do"       , "bool"               );

CView::checkin();
CView::enforceSlave();

$reports = array();

$error_reporting = error_reporting(0);

if ($do) {
  $steps = $times;
  while ($steps--) {
    // dummy query
    $user = new CUser();
    $user->countList();
    $ds = $user->getDS();
    $reports[] = array(
      "time"  => CMbDT::time(),
      "dsn"   => $ds->dsn,
      "errno" => $ds->errno(),
      "error" => $ds->error(),
    );
    sleep($duration);
  }
}

error_reporting($error_reporting);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("times", $times);
$smarty->assign("duration", $duration);
$smarty->assign("do", $do);
$smarty->assign("reports", $reports);
$smarty->display("slave_tester.tpl");