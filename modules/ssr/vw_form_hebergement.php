<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id = CValue::getOrSession("sejour_id");
$group_id  = CGroups::loadCurrent()->_id;

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->loadRefEtablissementTransfert();

CAccessMedicalData::logAccess($sejour);

$patient = $sejour->loadRefPatient();

$patient->loadRefsCorrespondants();
$patient->_ref_medecin_traitant->getExercicePlaces();
$medecin_adresse_par    = "";
$correspondantsMedicaux = array();
$sejour->loadRefAdresseParPraticien();
if ($sejour->adresse_par_prat_id && ($sejour->adresse_par_prat_id != $patient->_ref_medecin_traitant->_id)) {
  $sejour->_ref_adresse_par_prat->getExercicePlaces();
  $medecin_adresse_par = $sejour->_ref_adresse_par_prat;
}
if ($patient->_ref_medecin_traitant->_id) {
  $correspondantsMedicaux["traitant"] = $patient->_ref_medecin_traitant;
}
foreach ($patient->_ref_medecins_correspondants as $correspondant) {
  $correspondant->loadRefMedecin()->getExercicePlaces();
  $correspondantsMedicaux["correspondants"][] = $correspondant->_ref_medecin;
}

$service  = new CService();
$where    = array("group_id" => "= '$group_id'");
$order    = "nom";
$services = $service->loadListWithPerms(PERM_READ, $where, $order);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("medecin_adresse_par", $medecin_adresse_par);
$smarty->assign("services", $services);

$smarty->display("inc_form_hebergement");
