<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

ob_clean();

CApp::setMemoryLimit("1024M");
CApp::setTimeLimit(240);

$service_id   = CView::get("service_id", "ref class|CService");
$date         = CView::get("date", "date default|" . CMbDT::date());
$day_relative = CView::get('day_relative', 'num');

CView::checkin();
CView::enforceSlave();

$service = new CService();
$service->load($service_id);

if (!is_null($day_relative) && $day_relative >= 0) {
  $date = CMbDT::date("+$day_relative days", $date);
}

$datetime_min = "$date 00:00:00";
$datetime_max = "$date 23:59:59";
$datetime_avg = "$date " . CMbDT::time();

$sejour = new CSejour();
$where  = array();
$ljoin  = array();

$ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

$where["sejour.entree"]          = "<= '$datetime_max'";
$where["sejour.sortie"]          = " >= '$datetime_min'";
$where["affectation.entree"]     = "<= '$datetime_max'";
$where["affectation.sortie"]     = ">= '$datetime_min'";
$where["affectation.service_id"] = " = '$service_id'";

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);

$ordonnances = array();

foreach ($sejours as $_sejour) {
  $_prescription = $_sejour->loadRefPrescriptionSejour();
  $_patient      = $_sejour->loadRefPatient();

  $params = array(
    "prescription_id" => $_prescription->_id ?: "",
    "in_progress"     => 1,
    "multipart"       => 1
  );

  $_content      = CApp::fetch("dPprescription", "print_prescription_fr", $params);
  $_naissance    = str_replace("/", "-", $_patient->getFormattedValue("naissance"));
  $ordonnances[] = array(
    "title"     => base64_encode($_patient->_view . " - " . $_naissance),
    "content"   => base64_encode($_content),
    "extension" => "pdf",
  );
}

CApp::json($ordonnances);
