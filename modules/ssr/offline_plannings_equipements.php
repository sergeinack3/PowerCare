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
use Ox\Mediboard\Ssr\CPlateauTechnique;

CCanDo::checkRead();

CApp::setTimeLimit(240);
CApp::setMemoryLimit("1024M");

global $m;
$date = CView::get("date", "date default|now");
CView::enforceSlave();
CView::checkin();

$plannings   = array();
$equipements = array();

$where    = array();
$where[]  = "type = '$m' OR type IS NULL";
$plateau  = new CPlateauTechnique();
$plateaux = $plateau->loadGroupList($where);

/** @var CPlateauTechnique[] $plateaux */
foreach ($plateaux as $_plateau) {
  $_plateau->loadRefsEquipements();

  foreach ($_plateau->_ref_equipements as $_equipement) {
    if (!$_equipement->visualisable) {
      unset($_plateau->_ref_equipements[$_equipement->_id]);
      continue;
    }
    $equipements[$_equipement->_id] = $_equipement;
    $args_planning                  = array();
    $args_planning["equipement_id"] = $_equipement->_id;
    $args_planning["date"]          = $date;
    $plannings[$_equipement->_id]   = CApp::fetch("ssr", "ajax_planning_equipement", $args_planning);
  }
}
$monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("plannings", $plannings);
$smarty->assign("plateaux", $plateaux);
$smarty->assign("equipements", $equipements);
$smarty->assign("date", $date);
$smarty->assign("monday", $monday);
$smarty->assign("sunday", $sunday);
$smarty->display("offline_plannings_equipements");
