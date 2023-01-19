<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::read();

$service_id = CValue::get("service_id");
$date       = CValue::get("date", CMbDT::date());

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$_sejours = array();
$service  = new CService();

if (!$service_id) {
  return;
}

// Chargement des séjours à afficher
if ($service_id == "NP") {
  $ljoin                = array();
  $ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";
  $ljoin["patients"]    = "sejour.patient_id = patients.patient_id";

  $where = array();
  $g     = CGroups::loadCurrent()->_id;

  $where["entree_prevue"]   = " <= '$date 23:59:59'";
  $where["sortie_prevue"]   = ">= '$date 00:00:00'";
  $where["annule"]          = " = '0'";
  $where["type"]            = " != 'exte'";
  $where["sejour.group_id"] = "= '$g'";
  $where["sejour.type"]     = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence());
  $where[]                  = "affectation.affectation_id IS NULL";
  $order                    = "patients.nom, patients.prenom";

  $sejour   = new CSejour;
  $_sejours = $sejour->loadList($where, $order, null, null, $ljoin);
}
else {
  $service->load($service_id);
  loadServiceComplet($service, $date, 1);
  foreach ($service->_ref_chambres as $chambre) {
    foreach ($chambre->_ref_lits as $_lit) {
      foreach ($_lit->_ref_affectations as $_affectation) {
        $_sejours[] =& $_affectation->_ref_sejour;
      }
    }
  }
}

$outputs       = array();
$fiches_anesth = array();
foreach ($_sejours as $_sejour) {
  $consult_id = "";
  $_sejour->loadRefPatient();
  $_sejour->loadRefPraticien();

  $_sejour->loadRefsConsultAnesth();
  $_sejour->_ref_consult_anesth->loadRefConsultation();
  if ($_sejour->_ref_consult_anesth->_id && !$_sejour->_ref_consult_anesth->operation_id) {
    $consult_id = $_sejour->_ref_consult_anesth->_id;
  }
  else {
    $_sejour->loadRefsOperations();
    foreach ($_sejour->_ref_operations as $_operation) {
      $_operation->loadRefsConsultAnesth();
      if ($_operation->_ref_consult_anesth->_id) {
        $_operation->_ref_consult_anesth->loadRefConsultation();
        $consult_id = $_operation->_ref_consult_anesth->_id;
      }
    }
  }

  if ($consult_id) {
    $args_dossier                 = array(
      "dossier_anesth_id" => $consult_id,
      "offline"           => 1,
      "pdf"               => 0
    );
    $fiches_anesth[$_sejour->_id] = CApp::fetch("dPcabinet", "print_fiche", $args_dossier);
  }

  $args_fiche             = array(
    "sejour_id" => $_sejour->_id,
    "offline"   => 1
  );
  $outputs[$_sejour->_id] = CApp::fetch("soins", "print_dossier_soins", $args_fiche);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_sejours", $_sejours);
$smarty->assign("service_id", $service_id);
$smarty->assign("service", $service);
$smarty->assign("date", $date);
$smarty->assign("dateTime", CMbDT::dateTime());
$smarty->assign("outputs", $outputs);
$smarty->assign("fiches_anesth", $fiches_anesth);

$smarty->display("print_dossiers_service");
