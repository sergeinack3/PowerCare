<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CPrescriptionLine;

CCanDo::checkRead();

CApp::setTimeLimit(240);
CApp::setMemoryLimit("1024M");

$plannings = array();

global $m;
// Chargement de la liste des kines
$date = CView::get("date", "date default|now");
CView::enforceSlave();
CView::checkin();

$sunday = CMbDT::date("next sunday", CMbDT::date("- 1 DAY", $date));
$monday = CMbDT::date("-6 days", $sunday);

$where          = array();
$where["debut"] = "BETWEEN '$monday 00:00:00' and '$sunday 23:59:59'";

$mediuser                                = new CMediusers();
$ljoin                                   = array();
$ljoin["evenement_ssr"]                  = "evenement_ssr.therapeute_id = users_mediboard.user_id";
$where["evenement_ssr.evenement_ssr_id"] = "IS NOT NULL";

$group = "users_mediboard.user_id";

$kines = $mediuser->loadList($where, null, null, $group, $ljoin);

CPrescriptionLine::$_load_for_delete = true;

// Parcours des kines et chargement du planning
foreach ($kines as $_kine) {
  $args_planning                 = array();
  $args_planning["kine_id"]      = $_kine->_id;
  $args_planning["surveillance"] = 0;
  $args_planning["large"]        = 1;
  $args_planning["print"]        = 1;
  $args_planning["height"]       = 600;
  $args_planning["date"]         = $date;
  // Chargement du planning de technicien
  $plannings[$_kine->_id]["technicien"] = CApp::fetch("$m", "ajax_planning_technicien", $args_planning);

  // Chargement du planning de surveillance
  $args_planning["surveillance"] = 1;

  $plannings[$_kine->_id]["surveillance"] = CApp::fetch("$m", "ajax_planning_technicien", $args_planning);
}

$monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("plannings", $plannings);
$smarty->assign("kines", $kines);
$smarty->assign("date", $date);
$smarty->assign("monday", $monday);
$smarty->assign("sunday", $sunday);

$smarty->display("offline_plannings_techniciens");
