<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();
$version = CView::get("version", "str default|". CAppUI::conf('cim10 cim10_version'));
$page    = CView::get('page', "num default|0");
CView::checkin();
CView::enableSlave();

$codes = array();
$nb_sejours = 0;

if ($version == 'atih') {
  $codes = CCodeCIM10ATIH::getForbiddenCodes('mco', 'dp');
  $where = array();
  $ljoin = array();

  $sejour = new CSejour();
  $ljoin["patients"] =  "sejour.patient_id = patients.patient_id";
  $where["DP"] = CSQLDataSource::prepareIn($codes);
  $order = "sejour.entree ASC, patients.nom ASC";

  $sejours = $sejour->loadGroupList($where, $order, "$page, 25", null, $ljoin);
  $nb_sejours = $sejour->countList($where);

  CStoredObject::massLoadFwdRef($sejours, "patient_id");

  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPatient();
    $_sejour->loadDiagnosticsAssocies();
  }
}
else {
  CAppUI::stepAjax("La version de la CIM10 utilisée n'est pas celle de l'ATIH", UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("sejours"   , $sejours);
$smarty->assign("nb_sejours", $nb_sejours);
$smarty->assign("codes"     , $codes);
$smarty->assign("page"      , $page);
$smarty->display("inc_sejours_dp_forbidden.tpl");
