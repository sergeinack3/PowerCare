<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::check();

$operation_id = CView::get("operation_id", 'ref class|COperation', true);

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefs();
$operation->countExchanges();
$operation->isCoded();
$operation->canDo();
$operation->_ref_sejour->loadRefsFwd();
foreach ($operation->_ext_codes_ccam as $key => $value) {
  $operation->_ext_codes_ccam[$key] = CDatedCodeCCAM::get($value->code);
}
$operation->getAssociationCodesActes();
$operation->loadPossibleActes();
$operation->_ref_plageop->loadRefsFwd();
$operation->loadRefPraticien();

// Chargement des règles de codage
$operation->loadRefsCodagesCCAM();
foreach ($operation->_ref_codages_ccam as $_codages_by_prat) {
  /** @var CCodageCCAM $_codage */
  foreach ($_codages_by_prat as $_codage) {
    $_codage->loadPraticien()->loadRefFunction();
    $_codage->loadActesCCAM();
    $_codage->getTarifTotal();
    foreach ($_codage->_ref_actes_ccam as $_acte) {
      $_acte->getTarif();
    }
  }
}

// Chargement des praticiens
$listAnesths = new CMediusers;
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$listChirs = new CMediusers;
$listChirs = $listChirs->loadExecutantsCCAM(PERM_DENY);

//Initialisation d'un acte NGAP
$acte_ngap = CActeNGAP::createEmptyFor($operation);
// Liste des dents CCAM
$dents = CDentCCAM::loadList();
$liste_dents = reset($dents);

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

$group = CGroups::loadCurrent();
$group->loadConfigValues();

// Création du template
$smarty = new CSmartyDP("modules/dPsalleOp");

$smarty->assign("acte_ngap"    , $acte_ngap);
$smarty->assign("liste_dents"  , $liste_dents);
$smarty->assign("subject"      , $operation);
$smarty->assign("listAnesths"  , $listAnesths);
$smarty->assign("listChirs"    , $listChirs);
$smarty->assign('user'         , $user);
$smarty->assign("_is_dentiste" , $operation->_ref_chir->isDentiste());
$smarty->assign("codage_prat"  , $group->_configs["codage_prat"]);

$smarty->display("inc_codage_actes.tpl");
