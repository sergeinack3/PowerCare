<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$transmission = new CTransmissionMedicale();
$where        = array(
  "sejour_id"  => "= '$sejour_id'",
  "dietetique" => "= '0'"
);

$nb_trans_obs = $transmission->countList($where);

unset($where["dietetique"]);
$where["etiquette"] = "<> 'dietetique'";

$observation  = new CObservationMedicale();
$nb_trans_obs += $observation->countList($where);

unset($where["dietetique"]);
unset($where["etiquette"]);
$consultation    = new CConsultation();
$where["annule"] = "= '0'";
$nb_trans_obs    += $consultation->countList($where);
unset($where["annule"]);

// Compter les consultations d'anesthésie du séjour
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$consultations = $sejour->loadRefsConsultations();
CStoredObject::massCountBackRefs($consultations, "consult_anesth", array("operation_id" => "IS NOT NULL"));
foreach ($consultations as $_consult) {
  if ($_consult->_count["consult_anesth"] || $_consult->type == "entree") {
    $nb_trans_obs++;
  }
}

$consult_anesth = $sejour->loadRefsConsultAnesth();
if ($consult_anesth->operation_id) {
  $nb_trans_obs++;
}

$constantes = new CConstantesMedicales();
$where      = array(
  "context_class" => "= 'CSejour'",
  "context_id"    => "= '$sejour_id'"
);

$nb_trans_obs += $constantes->countList($where);

echo $nb_trans_obs;
