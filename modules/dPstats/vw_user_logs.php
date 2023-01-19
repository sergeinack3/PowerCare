<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$date     = CView::get("date", "date default|now");
$user_id  = CView::get("user_id", "ref class|CUser");
$interval = CView::get("interval", "enum list|one-week|eight-weeks|one-year|four-years");

CView::checkin();
CView::enforceSlave();

CAppUI::requireModuleFile("stats", "graph_userlog");

$to    = CMbDT::dateTime("+1 DAY", $date);
$graph = graphUserLog($to, $interval, $user_id);

// Chargement des utilisateurs
$user  = new CMediusers();
$users = $user->loadListFromType();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("graph", $graph);
$smarty->assign("date", $date);
$smarty->assign("user_id", $user_id);
$smarty->assign("users", $users);
$smarty->assign("interval", $interval);

$smarty->display("vw_user_logs.tpl");
