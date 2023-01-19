<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$old_patient_id  = CView::post('old_patient_id', 'ref class|CPatient notNull');
$new_patient_id  = CView::post('new_patient_id', 'ref class|CPatient notNull');
$all_backs_names = CView::post('all_backs_names', 'str notNull');
$abort           = CView::post('abort', 'bool default|0');

CView::checkin();

// If the user abort the unmerge, delete the created patient
if ($abort) {
  $new_patient = new CPatient();
  $new_patient->load($new_patient_id);
  if ($new_patient && $new_patient->_id) {
    if ($msg = $new_patient->delete()) {
      CAppUI::commonError($msg);
    }
    else {
      CAppUI::stepAjax('mod-dPpatients-unmerge-aborted');
    }
  }
  CApp::rip();
}

$old_patient = new CPatient();
$old_patient->load($old_patient_id);

$new_patient = new CPatient();
$new_patient->load($new_patient_id);

if (!$old_patient || !$old_patient->_id || !$new_patient || !$new_patient->_id) {
  CAppUI::commonError('CPatient.none');
}

// Get the backprops to bind
$parts = explode('|', $all_backs_names);
$backs = array();

foreach ($parts as $_back_name) {
  $back_spec = $old_patient->makeBackSpec($_back_name);
  if (!array_key_exists($_back_name, $backs)) {
    $backs[$_back_name] = array();
  }

  // Instanciate each backprop and change the patient_field
  foreach ($_POST as $_back_name_id => $_patient_id) {
    $store = true;
    if (strpos($_back_name_id, "$_back_name-") === 0) {
      $obj_name_id = explode('-', $_back_name_id);
      /** @var CStoredObject $obj */
      $obj = new $back_spec->class;
      $obj->load($obj_name_id[1]);

      if (!$obj || !$obj->_id) {
        continue;
      }

      // If the object is a Meta Object, use setobject
      if (property_exists($obj, 'object_id') && property_exists($obj, 'object_class') && method_exists($obj, 'setObject')) {
        if ($old_patient->_id == $_patient_id) {
          // Backprop déjà sur le patient
          $store = false;
        }
        elseif ($new_patient->_id == $_patient_id) {
          $obj->setObject($new_patient);
        }
        else {
          CApp::log('Pas de patient pour ' . $_back_name_id);
        }
      }
      else {
        // Else modify patient_field
        if ($old_patient->_id == $_patient_id) {
          // Backprop already for the patient
          $store = false;
        }
        elseif ($new_patient->_id == $_patient_id) {
          $obj->{$back_spec->field} = $new_patient->_id;
        }
        else {
          CApp::log('Pas de patient pour ' . $_back_name_id);
        }
      }

      // If backprop's patient_id changed save it
      if ($store) {
        $obj->_forwardRefMerging = true;
        if ($msg = $obj->store()) {
          CAppUI::stepAjax($msg, UI_MSG_WARNING);
        }
        else {
          CAppUI::stepAjax("$obj->_class-msg-modify", UI_MSG_OK);
        }
      }
    }
  }
}

$tag_unmerged = new CIdSante400();
$tag_unmerged->setObject($old_patient);
$tag_unmerged->tag   = "unmerged";
$tag_unmerged->id400 = $new_patient->_id;

if ($msg = $tag_unmerged->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

$ds = $old_patient->getDS();

$old_tag_merged = new CIdSante400();
$where          = array(
  'object_class' => "= 'CPatient'",
  'object_id'    => $ds->prepare('= ?', $old_patient->_id),
  'tag'          => "= 'merged'"
);
$old_tag_merged->loadObject($where, 'last_update DESC');

if (!$old_tag_merged || !$old_tag_merged->_id) {
  CAppUI::stepAjax('CIdSante400.none', UI_MSG_ERROR);
}

if ($msg = $old_tag_merged->delete()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}