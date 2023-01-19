<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;

CCanDo::checkAdmin();

$context_class = CView::post("context_class", "str");
$context_id    = CView::post("context_id", "num");
$patient_id    = CView::post("patient_id", "ref class|CPatient");

$datetime_start = CView::post("datetime_start", "dateTime");
$datetime_end   = CView::post("datetime_end", "dateTime");
$period         = CView::post("period", "num default|120"); // in seconds
CView::checkin();

$graph = new CSupervisionGraph();
/** @var CSupervisionGraph[] $graphs */
$graphs = $graph->loadList(
  array(
    "disabled" => "= '0'",
  )
);

$n        = 500;
$datetime = $datetime_start;
$times    = array();
while (--$n > 0 && ($datetime < $datetime_end)) {
  $observation_result_set                = new CObservationResultSet;
  $observation_result_set->context_class = $context_class;
  $observation_result_set->context_id    = $context_id;
  $observation_result_set->patient_id    = $patient_id;
  $observation_result_set->datetime      = $datetime;
  $observation_result_set->loadMatchingObject();
  $observation_result_set->store();

  $times[$datetime] = $observation_result_set;

  $datetime = CMbDT::dateTime("+$period SECONDS", $datetime);
}

foreach ($graphs as $_graph) {
  $_axes = $_graph->loadRefsAxes();

  foreach ($_axes as $_axis) {
    $_series = $_axis->loadRefsSeries();

    foreach ($_series as $_serie) {
      $_samples = $_serie->getSampleData(array_keys($times));

      foreach ($_samples as $_sample) {
        list($_datetime, $_value) = $_sample;

        $result                            = new CObservationResult;
        $result->observation_result_set_id = $times[$_datetime]->_id;
        $result->_unit_id                   = $_serie->value_unit_id ? $_serie->value_unit_id : "";
        $result->_value_type_id             = $_serie->value_type_id;
        $result->status                    = "I";
        $result->method                    = "SAMPLE";
        $result->loadMatchingObject();

        $result->_value = $_value;
        $result->store();
      }
    }
  }
}

CAppUI::stepAjax("Données de test générées", UI_MSG_OK);
CApp::rip();

