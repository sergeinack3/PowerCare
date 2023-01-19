<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$object_guid   = CView::get("object_guid", "str");
$timed_data_id = CView::get("timed_data_id", "ref class|CSupervisionTimedData");
CView::checkin();

/** @var COperation|CSejour $object */
$object = CStoredObject::loadFromGuid($object_guid);

CAccessMedicalData::logAccess($object);

$timed_data = new CSupervisionTimedData();
$timed_data->load($timed_data_id);

$result                = new CObservationResult();
$result->_value_type_id = $timed_data->value_type_id;
$result->loadRefValueType();

$result_set                = new CObservationResultSet();
$result_set->context_class = $object->_class;
$result_set->context_id    = $object->_id;
$result_set->datetime      = CMbDT::dateTime();
$result_set->patient_id    = $object->loadRelPatient()->_id;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("result", $result);
$smarty->assign("result_set", $result_set);
$smarty->assign("timed_data", $timed_data);

$smarty->display("inc_edit_observation_timed_data");
