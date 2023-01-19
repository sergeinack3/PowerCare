<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$dossier_anesth_id = CValue::get("dossier_anesth_id");
$sejour_id         = CValue::get("sejour_id");

if ($sejour_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $patient = $sejour->loadRefPatient();
}
else {
  $dossier_anesth = new CConsultAnesth();
  $dossier_anesth->load($dossier_anesth_id);
  $sejour = $dossier_anesth->loadRefSejour();
  $patient = $dossier_anesth->loadRefConsultation()->loadRefPatient();
}

// Chargement du dossier medical du sejour
$sejour->loadRefDossierMedical();

// Chargement du dossier medical du patient
$patient->loadRefDossierMedical();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("patient", $patient);

$smarty->display("inc_consult_anesth/inc_vw_facteurs_risque.tpl");
