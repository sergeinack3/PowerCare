<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

$sejours_id = CValue::get("sejours_id");

$sejours_by_type = array(
  "a_visiter" => array(),
  "effectue"  => array(),
);
if ($sejours_id) {
// Chargement de l'utilisateur courant
  $userCourant = CMediusers::get();

  $where = array();
  $ljoin = array();

  $where["sejour.sejour_id"] = CSQLDataSource::prepareIn(array_values($sejours_id));
  if ($userCourant->isPraticien()) {
    if ($userCourant->isAnesth()) {
      $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
      $ljoin["plagesop"]   = "plagesop.plageop_id = operations.plageop_id";
      $where[]             = "operations.anesth_id = '$userCourant->user_id' OR plagesop.anesth_id = '$userCourant->user_id'";
    }
    else {
      $where["sejour.praticien_id"] = " = '$userCourant->user_id'";
    }
  }
  $sejour = new CSejour();
  /* @var CSejour[] $sejours */
  $sejours = $sejour->loadList($where, null, null, null, $ljoin);

  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPatient();
    $visite_effectue                              = $_sejour->countNotificationVisite(null, $userCourant);
    $type_sejour                                  = $visite_effectue ? "effectue" : "a_visiter";
    $sejours_by_type[$type_sejour][$_sejour->_id] = $_sejour;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("sejours_by_type", $sejours_by_type);

$smarty->display("vw_visites_praticien");
