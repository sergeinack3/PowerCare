<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkRead();

$patient_id      = CView::get("patient_id", "ref class|CPatient");
$chir_id         = CView::get("chir_id", "ref class|CMediusers");
$plageconsult_id = CView::get("plageconsult_id", "ref class|CPlageconsult");
$heure           = CView::get("heure", "time");

CView::checkin();

$result = array();

$plage_date = new CPlageconsult();
$plage_date->load($plageconsult_id);

$ljoin = array();
$ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

$where = array();
$where["plageconsult.chir_id"]          = " <> '$chir_id'";
$where["plageconsult.plageconsult_id"]  = " <> '$plageconsult_id'";
$where["plageconsult.date"]             = " = '$plage_date->date'";
$where["consultation.patient_id"]       = " = '$patient_id'";
$heure_min = CMbDT::time("-1 HOURS", $heure);
$heure_max = CMbDT::time("+1 HOURS", $heure);
$where[] = "consultation.heure BETWEEN '$heure_min' AND '$heure_max'";

$consult = new CConsultation();
$consult->loadObject($where, "consultation.heure DESC", "consultation.consultation_id", $ljoin);

$result["date"] = "";
if ($consult->_id) {
  $consult->loadRefPraticien();
  $result["date"]    = CMbDT::transform($plage_date->date, null, CAppUI::conf("date"));
  $result["heure"]   = CMbDT::transform($consult->heure, null, "%Hh%M");
  $result["chir_id"] = $consult->_ref_praticien->_view;
}

CApp::json($result);
