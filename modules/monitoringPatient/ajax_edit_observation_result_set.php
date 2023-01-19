<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedData;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$object_guid   = CView::get("object_guid", "str");
$pack_id       = CView::get("pack_id", "ref class|CSupervisionGraphPack");
$result_set_id = CView::get("result_set_id", "ref class|CObservationResultSet");
$result_id     = CView::get("result_id", "str");
$datetime      = CView::get("datetime", "dateTime default|now");
$type          = CView::get("type", "str default|perop");
CView::checkin();

$result_set = new CObservationResultSet();
if ($result_set_id) {
  $result_set->load($result_set_id);

  $object = $result_set->loadRefContext();
}
else {
  /** @var COperation|CSejour $object */
  $object = CStoredObject::loadFromGuid($object_guid);

  CAccessMedicalData::logAccess($object);

  $result_set->context_class = $object->_class;
  $result_set->context_id    = $object->_id;
  $result_set->datetime      = $datetime;
  $result_set->patient_id    = $object->loadRelPatient()->_id;
}

[$results/*, $times*/] = SupervisionGraph::getResultsFor($object);

$pack = new CSupervisionGraphPack();
$pack->load($pack_id);
$links = $pack->loadRefsGraphLinks();

foreach ($links as $_link) {
  $_graph = $_link->loadRefGraph();

  if ($_graph instanceof CSupervisionGraph) {
    $axes = $_graph->loadRefsAxes(array("actif" => "='1'"));

    foreach ($axes as $_axis) {
      $series = $_axis->loadRefsSeries();
      $_axis->loadRefsLabels();
        foreach ($series as $_serie) {
            $_result = new CObservationResult();

            if ($result_set->_id) {
                if ($_serie->value_unit_id) {
                    $_result = CObservationResult::getObservationResultBySetAndValueType($result_set->_id, $_serie->value_type_id, $_serie->value_unit_id);
                } else {
                    $_result = CObservationResult::getObservationResultBySetAndValueType($result_set->_id, $_serie->value_type_id);
                }
            }

        $_result->loadUniqueValue();
        if (!$_result->_id) {
          $_result->_value_type_id = $_serie->value_type_id;
          $_result->_unit_id       = $_serie->value_unit_id;
        }
        $_result->_serie_title = $_serie->title ? $_serie->title : $_axis->_view;

        $_serie->_result = $_result;
      }
    }
  }
  elseif ($_graph instanceof CSupervisionTimedData) {
    $_result = new CObservationResult();

    if ($result_set->_id) {
        $_result = CObservationResult::getObservationResultBySetAndValueType($result_set->_id, $_graph->value_type_id);
    }

    $_result->loadUniqueValue();
      if (!$_result->_id) {
          $_result->_value_type_id = $_graph->value_type_id;
      }
    $_result->_values = preg_split('/[\r\n]+/', $_result->_value);
    CMbArray::removeValue('', $_result->_values);

    $_graph->makeItems();
    $_graph->_result = $_result;

    $_items = $_graph->_items ?: array();

    $diff = array_diff($_result->_values, $_items);

    $_graph->_items = array_merge($_items, $diff);
  }
  elseif ($_graph instanceof CSupervisionTimedPicture) {
    $_result                = new CObservationResult();

    if ($result_set->_id) {
        $_result = CObservationResult::getObservationResultBySetAndValueType($result_set->_id, $_graph->value_type_id);
    }
      $_result->loadUniqueValue();
      if (!$_result->_id) {
          $_result->_value_type_id = $_graph->value_type_id;
      }

    $_graph->loadRefsFiles();
    $_graph->_result = $_result;
  }
}

// Lock add new or edit observation
$limit_date_min = null;

if ($object instanceof COperation) {
  if ($object->entree_reveil && ($type == 'sspi')) {
    $limit_date_min = $object->entree_reveil;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object"        , $object);
$smarty->assign("result_set"    , $result_set);
$smarty->assign("pack"          , $pack);
$smarty->assign("result_id"     , $result_id);
$smarty->assign("results"       , $results);
$smarty->assign("limit_date_min", $limit_date_min);
$smarty->display("inc_edit_observation_result_set");

