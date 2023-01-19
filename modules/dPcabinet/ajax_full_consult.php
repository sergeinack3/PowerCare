<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamComp;
use Ox\Mediboard\Cabinet\CTechniqueComp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::check();
$user = CMediusers::get();

$consult_id        = CValue::get("consult_id");
$dossier_anesth_id = CValue::get("dossier_anesth_id");

if (!isset($current_m)) {
    global $m;
    $current_m = CValue::get("current_m", $m);
}

$listPrats = $listChirs = CConsultation::loadPraticiens(PERM_EDIT);

$listAnesths = $user->loadAnesthesistes();

$list_mode_sortie = [];

$consult = new CConsultation();
if ($current_m == "dPurgences") {
    if (!$consult_id) {
        CAppUI::setMsg("CConsultation-msg-You must select a consultation", UI_MSG_ALERT);
        CAppUI::redirect("m=urgences&tab=0");
    }

    $user      = CAppUI::$user;
    $group     = CGroups::loadCurrent();
    $listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);
}

// Test compliqué afin de savoir quelle consultation charger
if ($consult->load($consult_id) && $consult->patient_id) {
    $consult->loadRefPlageConsult();
}

CAccessMedicalData::logAccess($consult);

// On charge le praticien
$userSel = new CMediusers();
$userSel->load($consult->_ref_plageconsult->chir_id);
$userSel->loadRefs();
$canUserSel = $userSel->canDo();

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

$consultAnesth = $consult->loadRefConsultAnesth($dossier_anesth_id);

// Consultation courante
$consult->_ref_chir =& $userSel;

// Chargement de la consultation
if ($consult->_id) {
    $consult->canDo()->needsEdit(["consult_id" => null]);
    $consult->loadRefs();
    $consult->_ref_consult_anesth = $consultAnesth;

    // Chargement de la consultation préanesthésique

    // Chargement de la vue de chacun des dossiers
    foreach ($consult->_refs_dossiers_anesth as $_dossier) {
        $_dossier->loadRefConsultation();
        $_dossier->loadRefOperation()->loadRefPlageOp();
    }

    if (!is_array($consultAnesth) && $consultAnesth->_id) {
        if (CModule::getActive("maternite") &&
            $consult->grossesse_id && $consultAnesth->_id && !$consultAnesth->operation_id) {
            $grossesse = $consult->loadRefGrossesse();
            $sejours   = $grossesse->loadRefsSejours();
            if ($sejours) {
                $last_sejour              = end($sejours);
                $consultAnesth->sejour_id = $last_sejour->_id;
                if ($consult->_ref_consult_anesth) {
                    $consult->_ref_consult_anesth->sejour_id = $last_sejour->_id;
                }
            }
        }

        $consultAnesth->loadRefs();
        if ($consultAnesth->_ref_operation->_id || $consultAnesth->_ref_sejour->_id) {
            if ($consultAnesth->_ref_operation->passage_uscpo === null) {
                $consultAnesth->_ref_operation->passage_uscpo = "";
            }
            $consultAnesth->_ref_operation->loadExtCodesCCAM();
            $consultAnesth->_ref_operation->loadRefs();
            $consultAnesth->_ref_sejour->loadRefPraticien();
        }
    }

    // Chargement du patient
    $patient = $consult->_ref_patient;
    $patient->loadExternalIdentifiers();
    $patient->loadRefs();
    $patient->loadRefsNotes();
    $patient->loadRefPhotoIdentite();
    $patient->countBackRefs("consultations");
    $patient->countBackRefs("sejours");
    $patient->countINS();
    $patient->_ref_medecin_traitant->getExercicePlaces();

    // Si medecin correspondant devient le medecin traitant, ne pas l'afficher 2 fois
    foreach ($patient->_ref_medecins_correspondants as $_corresp) {
        if ($patient->medecin_traitant == $_corresp->medecin_id) {
            unset($patient->_ref_medecins_correspondants[$_corresp->_id]);
        }
    }

    foreach ($patient->_ref_medecins_correspondants as $_medecin_correspondant) {
        $_medecin_correspondant->loadRefMedecin()->getExercicePlaces();
    }

    // Chargement de ses consultations
    foreach ($patient->loadRefsConsultations() as $_consultation) {
        $_consultation->loadRefsFwd();
        $_consultation->_ref_chir->loadRefFunction()->loadRefGroup();
    }

    // Chargement de ses séjours
    foreach ($patient->loadRefsSejours() as $_sejour) {
        $_sejour->loadRefsFwd();
        $_sejour->loadRefsOperations();
        foreach ($_sejour->_ref_operations as $_operation) {
            $_operation->loadRefsFwd();
            $_operation->_ref_chir->loadRefFunction()->loadRefGroup();
        }
    }

    // Affecter la date de la consultation
    $date = $consult->_ref_plageconsult->date;
} else {
    $consultAnesth->_id = 0;
}

if ($consult->_id) {
    $consult->canDo();
}

// Chargement des FSE
if ($consult->_id && CModule::getActive("fse")) {
    $fse = CFseFactory::createFSE();
    if ($fse) {
        $fse->loadIdsFSE($consult);
        $fse->makeFSE($consult);

        $cps = CFseFactory::createCPS()->loadIdCPS($consult->_ref_chir);

        CFseFactory::createCV()->loadIdVitale($consult->_ref_patient);
    }
}

$antecedent     = new CAntecedent();
$traitement     = new CTraitement();
$techniquesComp = new CTechniqueComp();
$examComp       = new CExamComp();

$consult->loadExtCodesCCAM();
$consult->getAssociationCodesActes();
$consult->loadPossibleActes();
$consult->_ref_chir->loadRefFunction();
$consult->getCovidDiag();

// Chargement du dossier medical du patient de la consultation
$dossier_medical = new CDossierMedical();
if ($consult->patient_id) {
    $dossier_medical = $consult->_ref_patient->loadRefDossierMedical();
    $dossier_medical->updateFormFields();

    $dossier_medical->loadRefsAllergies();
    $dossier_medical->loadRefsAntecedents();
    $dossier_medical->countAntecedents(false);
    $dossier_medical->countAllergies();
}

// Chargement des actes NGAP
$consult->loadRefsActesNGAP();

// Chargement du medecin adressé par
if ($consult->adresse_par_prat_id) {
    $consult->loadRefAdresseParPraticien();
    $consult->_ref_adresse_par_prat->getExercicePlaces();
}

// Chargement des boxes
$services = [];

$sejour = $consult->loadRefSejour();

// Chargement du sejour
if ($consult->_ref_sejour && $sejour->_id) {
    $consult->_ref_sejour->loadRefCurrAffectation()->updateView();
    $sejour->loadExtDiagnostics();
    $sejour->loadRefDossierMedical();
    $sejour->loadNDA();

    // Cas des urgences
    $rpu = $sejour->loadRefRPU();
    if ($rpu && $rpu->_id) {
        // Mise en session du rpu_id
        $_SESSION["dPurgences"]["rpu_id"] = $rpu->_id;
        $rpu->loadRefSejourMutation();
        $sejour->loadRefCurrAffectation()->loadRefService();

        // Urgences pour un séjour "urg"
        if (in_array($sejour->type, CSejour::getTypesSejoursUrgence($sejour->praticien_id))) {
          $services = CService::loadServicesUrgence();
        }

        if ($sejour->_ref_curr_affectation->_ref_service->radiologie == "1") {
            $services = array_merge($services, CService::loadServicesImagerie());
        }

        // UHCD pour un séjour "comp" et en UHCD
        if ($sejour->type == "comp" && $sejour->UHCD) {
            $services = CService::loadServicesUHCD();
        }
    }
}

// Chargement du dossier de grossesse
if (CModule::getActive("maternite") && $consult->grossesse_id) {
    $grossesse = $consult->loadRefGrossesse();
    $consult->loadRefGrossesse()->loadRefParturiente();
    $grossesse->countBackRefs("depistages");
    $grossesse->countBackRefs("consultations");
    $grossesse->countBackRefs("echographies");
    $grossesse->countBackRefs("sejours");
    $grossesse->loadRefParturiente();
    $grossesse->loadRefDossierPerinat();
    $consult->getSuiviGrossesse();
    $consult->getSA();
}

// Tableau de contraintes pour les champs du RPU
// Contraintes sur le mode d'entree / provenance
//$contrainteProvenance[6] = array("", 1, 2, 3, 4);
$contrainteProvenance[7] = ["", 1, 2, 3, 4, 6];
$contrainteProvenance[8] = ["", 5, 8];

// Contraintes sur le mode de sortie / destination
$contrainteDestination["mutation"]  = ["", 1, 2, 3, 4];
$contrainteDestination["transfert"] = ["", 1, 2, 3, 4];
$contrainteDestination["normal"]    = ["", 6, 7];

// Contraintes sur le mode de sortie / orientation
$contrainteOrientation["mutation"]  = ["", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST"];
$contrainteOrientation["transfert"] = ["", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST"];
$contrainteOrientation["normal"]    = ["", "FUGUE", "SCAM", "PSA", "REO"];

$consult->loadRefGrossesse();
$consult->loadListEtatsDents();

// Tout utilisateur peut consulter en lecture seule une consultation de séjour
$consult->canEdit();

if ($consult->_ref_patient->_vip) {
    global $can;
    $can->denied();
}

if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($consult, $consult->loadRefGroup()->_id);
    $patient = $consult->loadRefPatient();
    $patient->loadRefStatusPatientUser();
}

if (CMOdule::getActive("maternite")) {
    $consult->_ref_patient->loadLastGrossesse();
}

$form_tabs = [];
if (CModule::getActive('forms')) {
    $objects = [
        ["tab_examen", $consult],
    ];

    if ($consultAnesth && $consultAnesth->_id) {
        $objects[] = ['tab_examen_anesth', $consultAnesth];
    }

    $form_tabs = CExClassEvent::getTabEvents($objects);
}

$consult->_ref_patient->loadRefLatestConstantes(null, [], null, false);

$consultAnesth->loadRefScoreLee();
$consultAnesth->loadRefScoreMet();

//Rcupration de la source labo
$source_labo = CExchangeSource::get(
    "OxLabo" . CGroups::loadCurrent()->_id,
    CSourceHTTP::TYPE,
    false,
    "OxLaboExchange",
    false
);

// Création du template
$smarty = new CSmartyDP("modules/dPcabinet");

$smarty->assign('form_tabs', $form_tabs);

$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("contrainteProvenance", $contrainteProvenance);
$smarty->assign("contrainteDestination", $contrainteDestination);
$smarty->assign("contrainteOrientation", $contrainteOrientation);

$smarty->assign("services", $services);

$smarty->assign("listAnesths", $listAnesths);
$smarty->assign("listChirs", $listChirs);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("date", $date);
$smarty->assign("userSel", $userSel);
$smarty->assign("anesth", $anesth);
$smarty->assign("consult", $consult);

$smarty->assign("getSourceLabo", $source_labo->active ? true : false);

if (CModule::getActive("maternite") && $consult->grossesse_id) {
    $smarty->assign("grossesse", $grossesse);
}

$smarty->assign("antecedent", $antecedent);
$smarty->assign("traitement", $traitement);
$smarty->assign("techniquesComp", $techniquesComp);
$smarty->assign("examComp", $examComp);
$smarty->assign("_is_anesth", $consult->_is_anesth);
$smarty->assign("_is_dentiste", $consult->_is_dentiste);
$smarty->assign("dossier_medical", $dossier_medical);
$smarty->assign("antecedents", $dossier_medical->_ref_antecedents_by_type);
$smarty->assign("synthese_rpu", "");
$smarty->assign("tabs_count", CConsultation::makeTabsCount($consult, $dossier_medical, $consultAnesth, $sejour));
$smarty->assign("constante", new CConstantesMedicales());

if (CModule::getActive("dPprescription") && CPrescription::isMPMActive()) {
    $smarty->assign("line", new CPrescriptionLineMedicament());
}

if (count($consult->_refs_dossiers_anesth)) {
    $nextSejourAndOperation = $consult->_ref_patient->getNextSejourAndOperation(
        $consult->_ref_plageconsult->date,
        true,
        $consult->_id
    );

    $secs = range(0, 60 - 1, 1);
    $mins = range(0, 15 - 1, 1);

    $smarty->assign("nextSejourAndOperation", $nextSejourAndOperation);
    $smarty->assign("secs", $secs);
    $smarty->assign("mins", $mins);
    $smarty->assign("consult_anesth", $consultAnesth);
    $smarty->display("inc_full_consult");
} else {
    $smarty->assign("consult_anesth", null);

    $smarty->display("inc_full_consult");
}
