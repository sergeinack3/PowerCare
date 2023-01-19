<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CDossierMedical;

CView::checkin();

$do = new CDoObjectAddEdit("CDossierMedical");

if ($_POST["del"] == 0) {
  // calcul de la valeur de l'id du dossier medical du patient
  $_POST["dossier_medical_id"] = CDossierMedical::dossierMedicalId($_POST["object_id"], $_POST["object_class"]);
}

$do->doIt();
