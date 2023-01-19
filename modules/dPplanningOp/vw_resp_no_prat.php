<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$repair = CValue::post('repair', 0);

$sejour_no_prat = array();
$sejour = new CSejour();

$ljoin = array();
$ljoin["users"] = "`users`.`user_id` = `sejour`.`praticien_id`";
  
$where = array();
// Type non Praticien, Anesthésiste, Medecin
$where["users.user_type"] = " != '13' AND users.user_type != '3' AND users.user_type != '4'";

$order = "sejour.sortie_reelle DESC";

if ($repair) {
  /** @var CSejour[] $sejours */
  $sejours = $sejour->loadList($where, $order, null, null, $ljoin);
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPraticien();
    $_sejour->loadNDA();
    $_sejour->loadRefsConsultations();
    $consult_atu = $_sejour->_ref_consult_atu;
    $consult_atu->loadRefPlageConsult();
    if ($consult_atu->_ref_chir->_id) {
      $_sejour->praticien_id = $consult_atu->_ref_chir->_id;
      $_sejour->store();
    }
  }
}

$sejours = $sejour->loadList($where, $order, null, null, $ljoin);
foreach ($sejours as $_sejour) {
  $_sejour->loadNDA();
  $_sejour->loadRefPraticien();
  $_sejour->loadRefsConsultations();
  $_sejour->_ref_consult_atu->loadRefPlageConsult();
  $sejour_no_prat[$_sejour->_ref_praticien->_id][] = $_sejour;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour_no_prat", $sejour_no_prat);
$smarty->assign("sejours"       , $sejours);

$smarty->display("vw_resp_no_prat");
