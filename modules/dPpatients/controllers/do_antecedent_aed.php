<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Mediboard\Patients\CDossierMedical;

$autoadd_default = CAppUI::pref("AUTOADDSIGN", true);
$del             = $_POST["del"];

// Sejour
// si on a un sejour et que l'option d'ajout automatique est activée
if (isset($_POST["_sejour_id"]) && $autoadd_default && ($_POST["_sejour_id"] != "")) {
  $doSejour = new CDoObjectAddEdit("CAntecedent");

  // Ajout de l'antecedent dans le sejour
  $_POST["dossier_medical_id"] = CDossierMedical::dossierMedicalId($_POST["_sejour_id"], "CSejour");
  $doSejour->redirectStore     = null;
  $doSejour->redirect          = null;

  $doSejour->doIt();
}

$_POST["del"] = $del;

// Patient
$doPatient = new CDoObjectAddEdit("CAntecedent");

if ($_POST["del"] != 1 && isset($_POST["_patient_id"])) {
  $_POST["dossier_medical_id"] = CDossierMedical::dossierMedicalId($_POST["_patient_id"], "CPatient");
}
$_POST["ajax"] = 1;

$doPatient->doIt();
