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
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$object_guid      = CValue::get("object_guid");
$timed_picture_id = CValue::get("timed_picture_id");

/** @var COperation|CSejour $object */
$object = CStoredObject::loadFromGuid($object_guid);

CAccessMedicalData::logAccess($object);

$timed_picture = new CSupervisionTimedPicture();
$timed_picture->load($timed_picture_id);
$timed_picture->loadRefsFiles();

$result                = new CObservationResult();
$result->_value_type_id = $timed_picture->value_type_id;
$result->loadRefValueType();
$result->_value = "FILE";

$result_set                = new CObservationResultSet();
$result_set->context_class = $object->_class;
$result_set->context_id    = $object->_id;
$result_set->datetime      = CMbDT::dateTime();
$result_set->patient_id    = $object->loadRelPatient()->_id;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("result", $result);
$smarty->assign("result_set", $result_set);
$smarty->assign("timed_picture", $timed_picture);

$smarty->display("inc_edit_observation_timed_picture.tpl");
