<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\COperationGarrot;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$operation_id     = CView::get("operation_id", "ref class|COperation", true);
$submitTiming     = CView::get("submitTiming", "str default|submitTiming", true);
$date             = CView::get("date", "date default|now", true);
$readonly         = CView::get('readonly', "bool default|0");
$operation_header = CView::get('operation_header', 'bool default|0');
$modal            = CView::get('modal', 'bool default|0');
$modif_operation  = $readonly ? false : (CCanDo::edit() || $date >= CMbDT::date());

CView::checkin();

$smarty = new CSmartyDP();

$operation = new COperation();
$postes = $nbOperations = 0;

if ($operation->load($operation_id)) {
  CAccessMedicalData::logAccess($operation);

  $operation->loadRefSejour();
  $operation->loadRefSalle();
  $operation->loadRefBrancardage();
  $operation->loadCurrRefBrancardage();
  $operation->loadLastRefBrancardage();
  $operation->loadRefsBrancardages();
  CStoredObject::massLoadBackRefs($operation->_ref_brancardages, "brancardage_ref_etapes");
  foreach($operation->_ref_brancardages as $brancardage) {
      $brancardage->loadRefEtapes();
  }

  $curr_group = CGroups::loadCurrent();

  if (CAppUI::conf('dPsalleOp COperation garrots_multiples', $curr_group)) {
    $operation->loadGarrots();
    $smarty->assign('garrot', new COperationGarrot());
  }

  if (CAppUI::conf("dPplanningOp COperation use_poste") && !$operation->sortie_reveil_reel && $operation->sspi_id) {
    $bloc_id         = $operation->_ref_salle->bloc_id;
    $poste           = new CPosteSSPI();
    $poste->type     = "sspi";
    $poste->sspi_id  = $operation->sspi_id;
    $postesSSPI      = $poste->loadMatchingList();
    $postes          = $poste->countMatchingList();

    $ljoin = array(
      "sallesbloc" => "sallesbloc.salle_id = operations.salle_id",
    );
    $where = array(
      "annulee"                  => "= '0'",
      "sallesbloc.bloc_id"       => " = '$bloc_id'",
      "sortie_salle"             => "IS NOT NULL",
      "sortie_reveil_reel"       => "IS NULL",
      "operations.date"          => "= '$date'",
      "operations.poste_sspi_id" => CSQLDataSource::prepareIn(array_keys($postesSSPI)),
    );

    // Chargement des interventions
    $nbOperations = $operation->countList($where, null, $ljoin);
  }

  if ($operation_header) {
    $operation->canDo();
    $operation->loadRefChir()->loadRefFunction();
    $operation->loadRefsConsultAnesth();
    $operation->loadRefsCommande();
    $sejour = $operation->_ref_sejour;

    $dossier_sejour = $sejour->loadRefDossierMedical();
    $dossier_sejour->loadRefsBack();
    $dossier_sejour->loadRefsAntecedents();
    $dossier_sejour->countAntecedents();
    $sejour->loadRefCurrAffectation()->updateView();

    if (CModule::getActive("maternite")) {
      $grossesse = $sejour->loadRefGrossesse();
      $grossesse->_ref_last_operation = $operation;
    }

    $patient = $sejour->loadRefPatient();
    $patient->loadRefPhotoIdentite();
    $patient->loadRefLatestConstantes(null, null, $sejour, false);

    $dossier_medical = $patient->loadRefDossierMedical();
    $dossier_medical->loadRefsAllergies();
    $dossier_medical->countAllergies();
  }
}

$one_timing_filled = false;

if (CAppUI::gconf("dPsalleOp COperation check_identity_pat")) {
  foreach (COperation::$timings as $_timing) {
    if ($operation->$_timing) {
      $one_timing_filled = true;
      break;
    }
  }
}

//permission
$currUser = CMediusers::get();
$allow_edit_sortie_salle = ( $currUser->_id ? CAppUI::loadPref('allow_edit_timing_sortie_salle', $currUser->_id) : false) && CCanDo::admin();

$smarty->assign("selOp"                  , $operation);
$smarty->assign("postes"                 , $postes);
$smarty->assign("nbOperations"           , $nbOperations);
$smarty->assign("date"                   , $date);
$smarty->assign("modif_operation"        , $modif_operation);
$smarty->assign("submitTiming"           , $submitTiming);
$smarty->assign("operation_header"       , $operation_header);
$smarty->assign("modal"                  , $modal);
$smarty->assign("one_timing_filled"      , $one_timing_filled);
$smarty->assign("allow_edit_sortie_salle", $allow_edit_sortie_salle);

$smarty->display("inc_vw_timing");
