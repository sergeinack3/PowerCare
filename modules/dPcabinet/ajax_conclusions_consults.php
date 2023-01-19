<?php
/**
 * @package Mediboard\Cabinet
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

$sejour_id  = CView::get("sejour_id", "num");
$consult_id = CView::get("consult_id", "num");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsConsultations();

foreach ($sejour->_ref_consultations as $_consult) {
  if ($_consult->_id == $consult_id) {
    unset($sejour->_ref_consultations[$_consult->_id]);
    continue;
  }
  $_consult->loadRefPlageConsult();
}

$smarty = new CSmartyDP();

$smarty->assign("sejour"    , $sejour);
$smarty->assign("consult_id", $consult_id);

$smarty->display("inc_conclusions_consults.tpl");