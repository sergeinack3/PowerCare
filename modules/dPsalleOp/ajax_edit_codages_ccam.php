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
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();
$module = CModule::getInstalled('dPccam');
$can    = $module->canDo();
$can->needsEdit();

$codable_class = CView::get('codable_class', 'str');
$codable_id    = CView::get('codable_id', 'ref meta|codable_class');
$praticien_id  = CView::get('praticien_id', 'ref class|CMediusers');
$date          = CView::get('date', 'date');

CView::checkin();

/** @var CCodable $codable */
$codable = CMbObject::loadFromGuid("{$codable_class}-{$codable_id}");

if ($codable instanceof CSejour || $codable instanceof COperation) {
    CAccessMedicalData::logAccess($codable);
}

if ($codable->_class != 'CSejour') {
    $codable->getActeExecution();
} else {
    $codable->_acte_execution = CMbDT::format($date, '%Y-%m-%d ') . CMbDT::format(CMbDT::dateTime(), '%H:%M:%S');

    if ($codable->_acte_execution < $codable->entree) {
        $codable->_acte_execution = $codable->entree;
    } elseif ($codable->_acte_execution > $codable->sortie) {
        $codable->_acte_execution = $codable->sortie;
    }
}

$remplace  = false;
$praticien = CMediusers::get($praticien_id);
if ($praticien->loadRefRemplacant($codable->_acte_execution)) {
    $remplace = $praticien;
    $remplace->loadRefFunction();
    $remplace->isAnesth();
    $praticien    = $praticien->_ref_remplacant;
    $praticien_id = $praticien->_id;
}

$codage = new CCodageCCAM();

$codage->codable_class = $codable_class;
$codage->codable_id    = $codable_id;
$codage->praticien_id  = $praticien->_id;
if ($date) {
    $codage->date = $date;
}

/** @var CCodageCCAM[] $codages */
$codages = $codage->loadMatchingList('activite_anesth desc');

/* Si aucun codage n'est trouvé, notamment dans le cas ou le praticien est remplacé, on créé alors le codage */
if (empty($codages) && $remplace) {
    $codage->store();
    /** @var CCodageCCAM[] $codages */
    $codages = $codage->loadMatchingList('activite_anesth desc');
}

foreach ($codages as $_codage) {
    $_codage->canDo();

    if (!$_codage->_can->edit) {
        CAppUI::accessDenied();
    }
    $_codage->loadPraticien()->loadRefFunction();
    $_codage->_ref_praticien->isAnesth();
    $_codage->loadActesCCAM();
    $_codage->getTarifTotal();
    $_codage->checkRules();

    foreach ($_codage->_ref_actes_ccam as $_acte) {
        $_acte->getTarif();
    }

    // Chargement du codable et des actes possibles
    $_codage->loadCodable();
    $codable   = $_codage->_ref_codable;
    $praticien = $_codage->_ref_praticien;
    $_codage->loadSibling();
}

$codable->isCoded();
$codable->loadRefPatient();
$codable->loadRefPraticien();
$codable->loadExtCodesCCAM();
//$codable->getAssociationCodesActes();
/* On charge les codages ccam du séjour en lui précisant une date pour ne pas qu'il charge tous les codages liés au sejour */
if ($codable->_class == 'CSejour') {
    /** @var \Ox\Mediboard\PlanningOp\CSejour $codable */
    $codable->loadRefsCodagesCCAM($date, $date);
}
if ($codable->_class == 'COperation' && CAppUI::gconf('dPccam codage display_ald_c2s')) {
    $codable->loadRefSejour();
}
$codable->loadPossibleActes($praticien_id);

$praticien->loadRefFunction();
$praticien->isAnesth();

$list_activites = [];
foreach ($codable->_ext_codes_ccam as $_code) {
    foreach ($_code->activites as $_activite) {
        if ($praticien->_is_anesth && $_activite->numero == 4) {
            $list_activites[$_activite->numero] = true;
        } elseif (!$praticien->_is_anesth && $_activite->numero != 4) {
            $list_activites[$_activite->numero] = true;
        } else {
            $list_activites[$_activite->numero] = false;
        }
    }
}

// Création du template
$smarty = new CSmartyDP();

//$smarty->assign("list_activites", $list_activites);
$smarty->assign("codages", $codages);
//$smarty->assign("codage", reset($codages));
$smarty->assign('subject', $codable);
$smarty->assign('praticien', $praticien);
$smarty->assign('remplace', $remplace);

$smarty->display("inc_edit_codages.tpl");
