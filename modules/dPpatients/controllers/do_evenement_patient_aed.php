<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Mediboard\Patients\CDossierMedical;

$do = new CDoObjectAddEdit("CEvenementPatient");

if ($_POST["del"] != 1 && isset($_POST["_patient_id"])) {
  $_POST["dossier_medical_id"] = CDossierMedical::dossierMedicalId($_POST["_patient_id"], "CPatient");
}
$_POST["ajax"] = 1;

$do->doIt();