<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$datetime    = CView::get("datetime", "dateTime default|now");
$prat_id     = CView::get("prat_id", "ref class|CMediusers");
$patient_id  = CView::get("patient_id", "ref class|CPatient");
$callback    = CView::get("callback", "str");
CView::checkin();

$date = CMbDT::format($datetime, "%Y-%m-%d");
$hour = CMbDT::format($datetime, "H:m:s");

$consultations = array();
if ($datetime && $prat_id && $patient_id) {
  $ljoin = array(
    "plageconsult" => "`plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`"
  );
  $where = array(
    "patient_id" => " = '$patient_id'",
    "chir_id" => " = '$prat_id'",
    "date" => " = '$date'",
    "consultation.type_consultation" => "= 'consultation'",
  );
  $order = "`consultation`.`heure` ASC";
  $limit = "0, 10";
  $consultations = new CConsultation();
  $consultations = $consultations->loadList($where, $order, $limit, null, $ljoin);
  CMbObject::massLoadBackRefs($consultations, "consult_anesth");
  foreach ($consultations as $_consultation) {
    $_consultation->loadRefConsultAnesth();
  }
}

$praticien = new CMediusers();
$praticien->load($prat_id);

$smarty = new CSmartyDP();
$smarty->assign("selected_date",      $datetime);
$smarty->assign("selected_praticien", $praticien);
$smarty->assign("consultations",      $consultations);
$smarty->assign("callback",           $callback);
$smarty->display("inc_consult_immediate_validation");
