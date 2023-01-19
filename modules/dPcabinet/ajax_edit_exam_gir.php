<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CExamGir;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$sejour_id   = CView::get("sejour_id", "ref class|CSejour notNull");
$exam_gir_id = CView::get("exam_gir_id", "ref class|CExamGir");
$creator_id  = CView::get("creator_id", "ref class|CMediusers notNull");
$digest      = CView::get("digest", "bool");

$_SESSION['soins']["selected_tab"] = "score_gir";
CView::checkin();

// Chargement du séjour
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargement du patient
$sejour->loadRefPatient();
$patient = $sejour->_ref_patient;

$date     = CMbDT::dateTime();

$exam_gir = new CExamGir();

if (($creator_id == CMediusers::get()->_id) && $exam_gir_id !== 0) {
  $exam_gir->load($exam_gir_id);
  $date = $exam_gir->date;
}
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("user_id", CMediusers::get()->_id);
$smarty->assign("exam_gir", $exam_gir);
$smarty->assign("digest", $digest);
$smarty->assign("date", $date);
$smarty->assign("variables_activites", $exam_gir::VARIABLES_ACTIVITES);

$smarty->display("exam_gir");
