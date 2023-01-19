<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CUserSejour;
use Ox\Mediboard\Soins\CTimeUserSejour;

CCanDo::checkEdit();
$date       = CView::get("date", "date default|now", true);
$sejour_id  = CView::get("sejour_id", "ref class|CSejour", true);
$service_id = CView::get("service_id", "ref class|CService", true);
$with_old   = CView::get("with_old", "bool default|1", true);
CView::checkin();

$group = CGroups::loadCurrent();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadRefsUserSejour(null, null, null, $with_old);

if (!CAppUI::conf("soins UserSejour see_global_users", $group)) {
  $user_affecte = $sejour->_ref_users_by_type;

  $user = CMediusers::get();
  $users = array(
    "infirmiere" => $user->loadListFromType(array("Infirmière")),
    "AS"    => $user->loadListFromType(array("Aide soignant")),
    "SF"    => $user->loadListFromType(array("Sage Femme")),
    "kine"  => $user->loadListFromType(array("Rééducateur")),
    "prat"  => $user->loadListFromType(array("Médecin", "Chirurgien", "Anesthésiste", "Dentiste")),
  );

  $type_affectation = CAppUI::conf("soins UserSejour type_affectation", $group);
  foreach ($user_affecte as $type => $users_affected) {
    foreach ($users_affected as $_user_sejour) {
      $_user = $_user_sejour->loadRefUser();
      if (isset($users[$type][$_user->_id]) && $type_affectation == "complet") {
        unset($users[$type][$_user->_id]);
      }
    }
  }
}
else {
  $users = array();

  $order_debut = CMbArray::pluck($sejour->_ref_users_sejour, "debut");
  $order_last_name = CMbArray::pluck($sejour->_ref_users_sejour, "_ref_user", "_user_last_name");
  array_multisort($order_debut, SORT_DESC,$order_last_name, SORT_ASC, $sejour->_ref_users_sejour);
}

$timing = new CTimeUserSejour();
$timings = $timing->loadGroupList(null, "name", null, "sejour_timing_id");

$user_sejour = new CUserSejour();
$user_sejour->_debut = $date;
$user_sejour->_fin = $user_sejour->_debut;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour"      , $sejour);
$smarty->assign("users"       , $users);
$smarty->assign("user_sejour" , $user_sejour);
$smarty->assign("refresh"     , CValue::get("refresh", 0));
$smarty->assign("timings"     , $timings);
$smarty->assign("service_id"  , $service_id);
$smarty->assign("with_old"    , $with_old);

$smarty->display("vw_affectations_sejour");
