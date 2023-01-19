<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientFamilyLink;

CCanDo::checkRead();
$patient_id  = CView::get("patient_id", "ref class|CPatient");
$parent_id_1 = CView::get("parent_id_1", "ref class|CPatient");
$parent_id_2 = CView::get("parent_id_2", "ref class|CPatient");
CView::checkin();

$families = array();
$where    = array();

$patient = new CPatient();
if (!$patient->load($patient_id)) {
  CApp::json(array());
}

$where[]             = "(parent_id_1 = '$parent_id_1' AND parent_id_2 = '$parent_id_2') OR 
            (parent_id_1 = '$parent_id_2' AND parent_id_2 = '$parent_id_1') OR
            (parent_id_1 = '$parent_id_1' AND parent_id_2 IS NULL) OR
            (parent_id_1 IS NULL AND parent_id_2 = '$parent_id_2')";
$where['patient_id'] = " <> '$patient->_id'";

$patient_family_link  = new CPatientFamilyLink();
$patient_family_links = $patient_family_link->loadList($where);

$i = 0;

foreach ($patient_family_links as $_family_link) {
  $_family_link->loadRefPatient();
  $id = $_family_link->_ref_patient->_id;

  $families[$i]["id"]   = $id;
  $families[$i]["view"] = $_family_link->_ref_patient->_view;

  $i++;
}

CApp::json($families);
