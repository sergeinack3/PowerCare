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
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$patient_id             = CView::get("patient_id", "ref class|CPatient");
$patient_ids_doublooons = CView::get("patient_ids_doublooons", "str");
$patient_ids_links      = CView::get("patient_ids_links", "str");
$status                 = CView::get("status", "enum list|VALI|PROV default|PROV");
$link                   = CView::get("link", "bool");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$patients = array(
  $patient->_id => $patient
);

$patients += $patient->loadList(array("patient_id" => CSQLDataSource::prepareIn($patient_ids_doublooons)));
$patients += $patient->loadList(array("patient_id" => CSQLDataSource::prepareIn($patient_ids_links)));

CPatient::massLoadIPP($patients);

$patients_ids = implode("-", CMbArray::pluck($patients, "patient_id"));

// Tri par statut (VALI > PROV > VIDE) et préselection de la première identité
usort(
  $patients,
  function ($patient1, $patient2) {
    switch ($patient1->status) {
      case "VALI":
        return false;
      case "PROV":
        return in_array($patient2->status, array("VALI", "PROV"));
      case "VIDE":
      default:
        return true;
    }
  }
);

$patients_ids_merge = CMbArray::pluck($patients, "_id");
$patient_id_ref     = reset($patients_ids_merge);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("patients", $patients);
$smarty->assign("patient_id_ref", $patient_id_ref);
$smarty->assign("patients_ids", $patients_ids);
$smarty->assign("status", $status);
$smarty->assign("link", $link);

$smarty->display("inc_merge_link_patients");