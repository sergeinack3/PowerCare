<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CBMRBHRe;

CView::checkin();

$do = new CDoObjectAddEdit("CBMRBHRe");

if ($_POST["del"] == 0) {
  // calcul de la valeur de l'id du dossier BMR BHRe du patient
  $_POST["bmr_bhre_id"] = CBMRBHRe::dossierId($_POST["patient_id"]);
}

$do->doIt();