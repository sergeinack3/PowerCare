<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Patients\CPatient;

CCanDo::check();
$observation_id = CView::get("observation_id", "ref class|CObservationMedicale");
$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$user_id        = CView::get("user_id", "ref class|CMediusers");
$select_diet    = CView::get("select_diet", "bool default|0");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$observation = new CObservationMedicale();

if (!$observation->load($observation_id)) {
  $observation->sejour_id = $sejour_id;
  $observation->user_id   = $user_id;
  $observation->date      = "now";
}

$observation->loadRefsNotes();
$observation->loadRefSejour()->loadRefPatient();

if ($select_diet && !$observation->_id) {
  $observation->etiquette = 'dietetique';
}

$smarty = new CSmartyDP();

$smarty->assign("observation", $observation);
$smarty->assign("patient", $observation->_ref_sejour->_ref_patient);
$smarty->assign("hour", CMbDT::format(CMbDT::time(), "%H"));

$smarty->display("inc_observation.tpl");
