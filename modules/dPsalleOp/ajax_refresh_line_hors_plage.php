<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();

$operation_id  = CView::get("operation_id", 'ref class|COperation');

CView::checkin();

$ds = CSQLDataSource::get("std");
$toRemove = false;

// bloc & salles
$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);
$salle = new CSalle();
$listSalles = $salle->loadListWithPerms(PERM_READ);

// anesths
$anesth = new CMediusers();
$anesths = $anesth->loadAnesthesistes(PERM_READ);

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$date = $operation->date;
if ($operation->plageop_id) {
  $toRemove = true;
}

if (!$toRemove) {
  $operation->loadRefsNotes();
  $operation->loadRefsFwd();
  $operation->loadRefAnesth();
  $patient = $operation->_ref_sejour->loadRefPatient();
  $dossier_medical = $patient->loadRefDossierMedical();
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->countAntecedents();
  $dossier_medical->countAllergies();
  $operation->_ref_chir->loadRefsFwd();
  $operation->isUrgence();

  // Chargement des plages disponibles pour cette intervention
  $operation->_ref_chir->loadBackRefs("secondary_functions");
  $secondary_functions = array();
  foreach ($operation->_ref_chir->_back["secondary_functions"] as $curr_sec_func) {
    $secondary_functions[$curr_sec_func->function_id] = $curr_sec_func;
  }
  $where = array();
  $selectPlages  = "(plagesop.chir_id = %1 OR plagesop.spec_id = %2
      OR plagesop.spec_id ".CSQLDataSource::prepareIn(array_keys($secondary_functions)).")";
  $where[]       = $ds->prepare($selectPlages, $operation->chir_id, $operation->_ref_chir->function_id);
  $where["date"] = "= '$date'";
  $where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
  $order = "salle_id, debut";
  $plage = new CPlageOp;
  $operation->_alternate_plages = $plage->loadList($where, $order);
  foreach ($operation->_alternate_plages as $curr_plage) {
    $curr_plage->loadRefChir();
    $curr_plage->loadRefAnesth();
    $curr_plage->loadRefSpec();
    $curr_plage->loadRefSalle();
  }
}

// Liste des types d'anesthésie
$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

$smarty = new CSmartyDP("modules/dPsalleOp");
$smarty->assign("op"  , $operation);
$smarty->assign("anesths",    $anesths);
$smarty->assign("listSalles", $listSalles);
$smarty->assign("listBlocs",  $listBlocs);
$smarty->assign("to_remove",   $toRemove);
$smarty->assign("listAnesthType" , $listAnesthType);

$smarty->display("inc_line_hors_plage");
