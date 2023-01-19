<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CUserSejour;
use Ox\Mediboard\Soins\CTimeUserSejour;

CCanDo::checkEdit();
$date        = CView::get("date", "date default|now", true);
$sejour_ids  = CView::get("sejour_ids", "str", true);
$service_id  = CView::get("service_id", "ref class|CService", true);
$with_old    = CView::get("with_old", "bool default|1", true);
$only_list   = CView::get("only_list", "bool default|0");
CView::checkin();

/* @var CSejour[] $sejours*/
$sejours = array();
$sejour_ids = json_decode(utf8_encode(stripslashes($sejour_ids)), true);
foreach ($sejour_ids as $_sejour_json) {
  if ($_sejour_json["_checked"]) {
    /* @var CSejour $sejour*/
    $sejour = CMbObject::loadFromGuid($_sejour_json["line_guid"]);
    $sejours[$sejour->_id] = $sejour;
  }
}

foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefsUserSejour(null, null, null, $with_old);
}

$timing = new CTimeUserSejour();
$timings = $timing->loadGroupList(null, "name", null, "sejour_timing_id");

$user_sejour = new CUserSejour();
$user_sejour->_debut = $date;
$user_sejour->_fin = $user_sejour->_debut;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours"     , $sejours);
$smarty->assign("user_sejour" , $user_sejour);
$smarty->assign("timings"     , $timings);
$smarty->assign("service_id"  , $service_id);
$smarty->assign("ids_sejour"  , implode('|', array_keys($sejours)));
$smarty->assign("with_old"    , $with_old);

if ($only_list) {
  $smarty->display("list_sejours_affectations_multiple");
}
else {
  $smarty->display("vw_affectations_multiple_personnel");
}
