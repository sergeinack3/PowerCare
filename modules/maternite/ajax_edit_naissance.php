<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Modification de naissance
 */

CCAnDo::checkEdit();

$naissance_id = CView::get("naissance_id", "ref class|CNaissance");
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$operation_id = CView::get("operation_id", "ref class|COperation");
$provisoire   = CView::get("provisoire", "bool default|0");
$callback     = CView::get("callback", "str");

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$constantes = new CConstantesMedicales();

$patient            = new CPatient();
$patient->naissance = CMbDT::date();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$parturiente = $sejour->loadRefPatient();

$anonmymous = $parturiente ? is_numeric($parturiente->nom) : false;

$naissance = new CNaissance();
$naissance->_provisoire = $provisoire;

if ($naissance->load($naissance_id)) {
  // Quand la naissance existe, le praticien à modifier est
  // celui du séjour de l'enfant.
  $sejour     = $naissance->loadRefSejourEnfant();
  $patient    = $sejour->loadRefPatient();
  $constantes = $patient->getFirstConstantes();

  // Heure courante sur la naissance et date courante sur le patient
  // pour transformer le dossier provisoire en naissance
  if (!$naissance->date_time) {
    $naissance->date_time = CMbDT::dateTime();
    $patient->naissance   = CMbDT::date();
  }
}
else {
  $grossesse = $sejour->loadRefGrossesse();

  if ($provisoire) {
    $patient->naissance = CAppUI::conf("maternite CNaissance now_naissance_provi", "CGroups-$sejour->group_id") ? CMbDT::date() : $grossesse->terme_prevu;
  }
  else {
    $patient->naissance   = CMbDT::date();
    $naissance->date_time = CMbDT::dateTime();
    $naissance->rang      = $grossesse->countBackRefs("naissances") + 1;
  }

  $naissance->sejour_maman_id = $sejour_id;
  $naissance->operation_id    = $operation_id;

  // guess cesarienne
  $op = new COperation();
  $op->load($operation_id);
  if ($op->_id) {
    $naissance->sejour_maman_id = $op->loadRefSejour()->_id;
  }

  $bloc = $op->loadRefSalle()->loadRefBloc();
  if ($bloc->_id && $bloc->type != "obst") {
    $naissance->by_caesarean = "1";
  }

  if (!$anonmymous) {
    $patient->nom = $parturiente->nom;
  }
}
$patient->loadCodeInseeNaissance();
$patient->updateNomPaysInsee();
$naissance->num_naissance = $naissance->getNumNaissance();

$maman         = $naissance->loadRefSejourMaman()->loadRefPatient();
$sejours_maman = $maman->loadRefsSejours(array("annule" => "= '0'"));

$sejour->loadRefPraticien();

$service            = new CService();
$where              = array();
$where["group_id"]  = "= '" . CGroups::loadCurrent()->_id . "'";
$where["cancelled"] = "= '0'";
$services           = $service->loadListWithPerms(PERM_READ, $where, "nom");

$ufs = CUniteFonctionnelle::getUFs($sejour);

$smarty = new CSmartyDP();
$smarty->assign("naissance", $naissance);
$smarty->assign("patient", $patient);
$smarty->assign("constantes", $constantes);
$smarty->assign("parturiente", $parturiente);
$smarty->assign("provisoire", $provisoire);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("callback", $callback);
$smarty->assign("sejour", $sejour);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("list_constantes", CConstantesMedicales::$list_constantes);
$smarty->assign("services", $services);
$smarty->assign("ufs", $ufs);
$smarty->assign("sejours_maman", $sejours_maman);
$smarty->assign("cpi_list", CChargePriceIndicator::getList("comp"));

$smarty->display("inc_edit_naissance");
