<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CActeCCAM;

CCanDo::checkEdit();
// Utilisateur sélectionné ou utilisateur courant
$prat_id       = CView::getRefCheckRead("chirSel", "ref class|CMediusers");
$selConsult    = CView::get("selConsult", "ref class|CConsultation");
$view          = CView::get('view', 'enum list|cabinet|oxCabinet default|cabinet');
$only_cotation = CView::get("only_cotation", "bool default|0");
$cotation_full = CView::get("cotation_full", "bool default|0");

CView::checkin();

$userSel = CMediusers::get($prat_id);
$userSel->loadRefFunction();
$userSel->loadRefSpecCPAM();
$userSel->loadRefDiscipline();
$canUserSel = $userSel->canDo();

// Vérification des droits sur les praticiens
$listChir = CConsultation::loadPraticiens(PERM_EDIT);

if (!$userSel->isMedical()) {
    CAppUI::setMsg("Vous devez selectionner un personnel de santé", UI_MSG_ALERT);
    CAppUI::redirect("m=dPcabinet&tab=0");
}

$canUserSel->needsEdit();

// Consultation courante
$consult            = new CConsultation();
$consult->_ref_chir = $userSel;
if ($selConsult) {
    $consult = CConsultation::findOrFail($selConsult);

    CAccessMedicalData::logAccess($consult);

    $canConsult = $consult->canDo();
    $canConsult->needsEdit();

    // Some Forward references
    $consult->loadRefPatient();
    $consult->loadRefPraticien();
    $consult->_ref_patient->loadRefsCorrespondants();
    $consult->loadRefsActes();
    $consult->loadExtCodesCCAM();
    $consult->loadRefFacture()->loadRefsReglements();
    $consult->bindTarif();
    $accident_travail = $consult->loadRefAccidentTravail();

    if (!$accident_travail->_id) {
        $accident_travail->object_class = $consult->_class;
        $accident_travail->object_id    = $consult->_id;
    }

    if (!$consult->org_at) {
        $patient         = $consult->_ref_patient;
        $consult->org_at = '' . $patient->code_regime . $patient->caisse_gest . $patient->centre_gest;
    }
}

if (CModule::getActive("fse")) {
    $fse = CFseFactory::createFSE();
    if ($fse) {
        $fse->loadIdsFSE($consult);
        $fse->makeFSE($consult);
        CFseFactory::createCPS()->loadIdCPS($consult->_ref_chir);
        CFseFactory::createCV()->loadIdVitale($consult->_ref_patient);
    }
}

// Récupération des tarifs
$tarifs = [];
$list_devis = [];
if (!$consult->valide) {
    $tarifs = CTarif::loadTarifsUser($userSel);

    $list_devis = $consult->loadBackRefs('devis_codage', 'creation_date ASC', null, 'devis_codage_id');
}

//Recherche de la facture pour cette consultation
$facture = $consult->_ref_facture;
$divers  = [];
if (CAppUI::gconf("dPccam frais_divers use_frais_divers_CConsultation")) {
    $divers = $consult->loadRefsFraisDivers(count($consult->_ref_factures) + 1);
    $consult->loadRefsFraisDivers(null);
}

if ($facture->_id) {
    $facture->loadRefPatient();
    $facture->loadRefPraticien();
    $facture->loadRefAssurance();
    $facture->loadRefsObjects();
    $facture->loadRefsReglements();
    $facture->loadRefsRelances();
    $facture->loadRefsNotes();
    $facture->loadCoefficients();
    $facture->loadRefCategory();
    $facture->loadFileXML();
    $facture->loadRefExtourne();
}

$opened_factures = $facture->loadList(
    [
        "praticien_id" => "= '" . $consult->_ref_chir->_id . "'",
        "patient_id"   => "= '$consult->patient_id'",
        "cloture IS NULL",
        "facture_id"   => "<> '$facture->_id'",
        "annule"       => "= '0'",
        "extourne"     => "= '0'",
    ]
);
$today           = CMbDT::date();
$past_consults   = [];
$past_intervs    = [];
if (!$consult->sejour_id) {
    $where_consult = [
        'sejour_id'                       => ' IS NULL',
        "annule"                          => " = '0'",
        "functions_mediboard.function_id" => " = '$userSel->function_id'",
    ];

    $consultations = $consult->_ref_patient->loadRefsConsultations($where_consult);
    CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
    foreach ($consultations as $_consultation) {
        $_consultation->loadRefPlageConsult();
        if ($_consultation->_date < $today) {
            $_consultation->loadRefFacture()->loadRefsReglements();
            if ($_consultation->_ref_facture->_du_restant_patient > 0 && !$_consultation->_ref_facture->patient_date_reglement) {
                $past_consults[$_consultation->_id] = $_consultation;
            }
        }
    }

    $sejours = $consult->_ref_patient->loadRefsSejours(["annule = '0'"]);
    CStoredObject::massLoadBackRefs($sejours, "operations");
    foreach ($sejours as $_sejour) {
        $operations = $_sejour->loadRefsOperations();
        CStoredObject::massLoadBackRefs($operations, "actes_ccam");
        foreach ($operations as $_operation) {
            if ($_operation->annulee || $_operation->date >= $today) {
                continue;
            }

            /** @var CActeCCAM $_acte_ccam */
            foreach ($_operation->loadRefsActesCCAM() as $_acte_ccam) {
                if ($_acte_ccam->executant_id == $consult->_ref_plageconsult->chir_id
                    && $_acte_ccam->montant_depassement && !$_acte_ccam->regle_dh
                ) {
                    $_operation->_actes_non_regles[] = $_acte_ccam;
                    $past_intervs[$_operation->_id]  = $_operation;
                }
            }
        }
    }

    CStoredObject::massLoadFwdRef($past_intervs, "plageop_id");
    /** @var COperation $_interv */
    foreach ($past_intervs as $_interv) {
        $_interv->loadRefPlageOp();
    }
}

// Reglement vide pour le formulaire
$reglement = new CReglement();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("frais_divers", $divers);
$smarty->assign("facture", $facture);
$smarty->assign("consult", $consult);
$smarty->assign("reglement", $reglement);
$smarty->assign("tarifs", $tarifs);
$smarty->assign("list_devis", $list_devis);
$smarty->assign("date", CMbDT::date());
$smarty->assign('view', $view);
$smarty->assign('past_consults', $past_consults);
$smarty->assign("past_intervs", $past_intervs);
$smarty->assign("opened_factures", $opened_factures);
$smarty->assign('user', CMediusers::get());
$smarty->assign("only_cotation", $only_cotation);
$smarty->assign("cotation_full", $cotation_full);
$smarty->display("inc_facturation");
