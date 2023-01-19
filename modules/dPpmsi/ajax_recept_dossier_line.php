<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$field     = CView::get("field", "str");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if (!$field) {
  $sejour->loadRefPatient();
  $sejour->loadRefPraticien();
  $sejour->loadRefsOperations(array("operations.annulee" => "= '0'"));
  $sejour->loadNDA();
  $sejour->loadRefRelance();
}

$smarty = new CSmartyDP();

if (!$field) {
  $smarty->assign("_sejour" , $sejour);
  $smarty->display("reception_dossiers/inc_recept_dossier_line");
}
else {
  $smarty->assign("field" , $field);
  $smarty->assign("sejour", $sejour);
  $smarty->display("inc_sejour_dossier_completion");
}