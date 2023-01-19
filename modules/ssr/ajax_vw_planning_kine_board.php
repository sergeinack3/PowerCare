<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$kine_id   = CView::get("kine_id", "str", true);
$date      = CView::get("date", "date", true);
$current_m = CView::get("current_m", "str default|ssr", true);
CView::checkin();

$group_id = CGroups::loadCurrent()->_id;
$monday   = CMbDT::date("last monday", $date);

// Chargement des evenements de la semaine precedente qui n'ont pas encore ete validés
$ljoin                          = array();
$ljoin["sejour"]                = "sejour.sejour_id = evenement_ssr.sejour_id";
$where                          = array();
$where["evenement_ssr.realise"] = " = '0'";
$where["evenement_ssr.annule"]  = " = '0'";
$where[]                        = "sejour.sejour_id IS NOT NULL";
$where["sejour.type"]           = " = '$current_m'";
$where["sejour.group_id"]       = " = '$group_id'";
$where[]                        = "evenement_ssr.therapeute_id = '$kine_id' AND evenement_ssr.debut < '$monday 00:00:00'";
$evenement                      = new CEvenementSSR();
$count_evts                     = $evenement->countList($where, null, $ljoin);
$count_by_week                  = CEvenementSSR::countByWeekNbNotValide($kine_id, $monday, $current_m);

$kine = new CMediusers();
$kine->load($kine_id);
$kine->canDo();
$kines = CEvenementSSR::loadRefExecutants(CGroups::loadCurrent()->_id);
if (isset($kines[$kine_id])) {
  unset($kines[$kine_id]);
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("kine_id", $kine_id);
$smarty->assign("kine", $kine);
$smarty->assign("count_evts", $count_evts);
$smarty->assign("count_by_week", $count_by_week);
$smarty->assign("current_m", $current_m);
$smarty->assign("kines", $kines);
$smarty->display("inc_vw_planning_kine_board");
