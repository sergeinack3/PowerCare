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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();
$object_class    = CView::get("object_class", "str", true);
$object_id       = CView::get("object_id", "ref meta|object_class", true);
$module          = CView::get("module", "str", true);
$do_subject_aed  = CView::get("do_subject_aed", "str", true);
$chir_id         = CView::get("chir_id", "ref class|CMediusers", true);
$date            = CView::get("date", "date default|now", true);
CView::checkin();

// Chargement de la liste des praticiens
$listChirs = new CMediusers;
$listChirs = $listChirs->loadExecutantsCCAM(PERM_DENY);

// Chargement de la liste des anesthesistes
$listAnesths = new CMediusers;
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

// Liste des dents CCAM
$dents = CDentCCAM::loadList();
$liste_dents = reset($dents);

/** @var CCodable $codable */
$codable = new $object_class;
$codable->load($object_id);

if ($codable instanceof CSejour || $codable instanceof COperation || $codable instanceof CConsultation) {
  CAccessMedicalData::logAccess($codable);
}

$codable->isCoded();

$codable->countActes();
$codable->loadRefPatient();
$codable->loadRefPraticien();
$codable->loadExtCodesCCAM();
$codable->getAssociationCodesActes();
$codable->getActeExecution();
$codable->loadPossibleActes();
$codable->canDo();
if ($codable->_class == "COperation") {
  $codable->countExchanges();
}

if ($codable->_class == "CConsultation") {
  $codable->loadRefSejour()->loadDiagnosticsAssocies();
}

$codable->loadRefsCodagesCCAM();
foreach ($codable->_ref_codages_ccam as $_codages_by_prat) {
  foreach ($_codages_by_prat as $_codage) {
    $_codage->loadPraticien()->loadRefFunction();
    $_codage->loadActesCCAM();
    $_codage->getTarifTotal();
    foreach ($_codage->_ref_actes_ccam as $_acte) {
      $_acte->getTarif();
    }
  }
}

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("listAnesths"      , $listAnesths);
$smarty->assign("listChirs"        , $listChirs);
$smarty->assign("liste_dents"      , $liste_dents);
$smarty->assign("subject"          , $codable);
$smarty->assign("module"           , $module);
$smarty->assign("do_subject_aed"   , $do_subject_aed);
$smarty->assign("chir_id"          , $chir_id);
$smarty->assign('user'         , $user);
$smarty->display("inc_codage_ccam.tpl");
