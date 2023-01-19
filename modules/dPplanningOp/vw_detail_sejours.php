<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$protocole_id_stat = CView::get("protocole_id_stat", "ref class|CProtocole");
$debut_stat        = CView::get("debut_stat", "date");
$fin_stat          = CView::get("fin_stat", "date");
$page              = CView::get("page", "num default|0");

CView::checkin();

$protocole = new CProtocole();
$protocole->load($protocole_id_stat);

$sejour = new CSejour();

$where = array(
  "protocole_id" => "= '$protocole_id_stat'",
  "DATE(entree) <= '$fin_stat'",
  "DATE(sortie) >= '$debut_stat'"
);

$ljoin = array(
  "operations" => "operations.sejour_id = sejour.sejour_id"
);

$sejours = $sejour->loadList($where, "entree DESC", "$page,30", null, $ljoin);

$total = $sejour->countList($where, null, $ljoin);

CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours"   , $sejours);
$smarty->assign("protocole" , $protocole);
$smarty->assign("total"     , $total);
$smarty->assign("page"      , $page);
$smarty->assign("debut_stat", $debut_stat);
$smarty->assign("fin_stat"  , $fin_stat);

$smarty->display("vw_detail_sejours");