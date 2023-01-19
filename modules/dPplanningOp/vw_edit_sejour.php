<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

if (CAppUI::pref("create_dhe_with_read_rights")) {
    CCanDo::checkRead();
} else {
    CCanDo::checkEdit();
}

global $dialog;

$contextual_call       = CView::get("contextual_call", "bool default|0");
$sejour_id             = CView::get("sejour_id", "ref class|CSejour", true);
$patient_id            = CView::get("patient_id", "ref class|CPatient");
$praticien_id          = CView::get("praticien_id", "ref class|CMediusers", CAppUI::conf("dPplanningOp CSejour use_session_praticien"));
$grossesse_id          = CView::get("grossesse_id", "ref class|CGrossesse");
$consult_related_id    = CView::get("consult_related_id", "ref class|CConsultation");
$protocole_id          = CView::get("protocole_id", "ref class|CProtocole");
$mutation              = CView::get("mutation", "bool");
$dhe_mater             = CView::get("dhe_mater", "bool");
$date_reservation      = CView::get("date_reservation", "date");
$ext_cabinet_id        = CView::get("cabinet_id", "num", true);
$ext_patient_id        = CView::get("ext_patient_id", "num", true);
$ext_patient_nom       = CView::get("ext_patient_nom", "str", true);
$ext_patient_prenom    = CView::get("ext_patient_prenom", "str", true);
$ext_patient_naissance = CView::get("ext_patient_naissance", "date", true);

CView::checkin();

CAccessMedicalData::logAccess("CConsultation-$consult_related_id");

// Liste des Etablissements selon Permissions
$etablissements = new CMediusers();
$etablissements = $etablissements->loadEtablissements(PERM_READ);

// L'utilisateur est-il un praticien
$mediuser = CMediusers::get();
if ($mediuser->isPraticien() and !$praticien_id) {
    $praticien_id = $mediuser->user_id;
}

// Chargement du praticien
$praticien = new CMediusers();
if ($praticien_id) {
    $praticien->load($praticien_id);
}

// Chargement du patient
$patient = new CPatient();
if ($patient_id) {
    $patient->load($patient_id);
}

// On récupére le séjour
$sejour                     = new CSejour();
$sejour->_ref_patient       = $patient;
$sejour->consult_related_id = $consult_related_id;

if ($sejour_id) {
    $sejour->load($sejour_id);

    if (CBrisDeGlace::isBrisDeGlaceRequired() && !CAccessMedicalData::checkForSejour($sejour)) {
        CAppUI::accessDenied();
    } else {
        if (!$sejour->canDo()->read) {
            global $m, $tab;
            CAppUI::setMsg("Vous n'avez pas accés à ce séjour", UI_MSG_WARNING);
            CAppUI::redirect("m=$m&tab=$tab&sejour_id=0");
            CAppUI::accessDenied();
        }
    }

    $sejour->loadRefPatient();
    $sejour->loadRefPraticien()->canDo();
    $sejour->loadRefEtablissement();
    $sejour->loadRefEtablissementTransfert();
    $sejour->loadRefServiceMutation();
    $sejour->loadRefsAffectations();
    $sejour->loadRefsOperations();
    $sejour->loadRefCurrAffectation()->loadRefService();
    $sejour->_ref_curr_affectation->updateView();
    $sejour->updateFieldsFacture();
    $sejour->loadRefMedecinTraitant();

    foreach ($sejour->_ref_operations as $operation) {
        $operation->loadRefPlageOp();
        $operation->loadExtCodesCCAM();
        $operation->loadRefsConsultAnesth();
        $operation->loadRefChirs();
        $operation->loadRefPatient();
        $operation->_ref_chir->loadRefFunction();
        $operation->_ref_chir->loadRefSpecCPAM();
        $operation->_ref_chir->loadRefDiscipline();
        $operation->loadRefBrancardage();
    }

    foreach ($sejour->_ref_affectations as $affectation) {
        $affectation->loadView();
    }
    $praticien = $sejour->_ref_praticien;
    $patient   = $sejour->_ref_patient;
}

$sejour->makeDatesOperations();
$sejour->loadRefsNotes();
$sejour->loadRefsConsultAnesth();
$sejour->_ref_consult_anesth->loadRefConsultation();
$sejour->loadRefEtablissementProvenance();

$dhe_urgences_lite = CAppUI::gconf("dPplanningOp CSejour dhe_urgences_lite");

if ($dhe_urgences_lite && $mutation) {
    if (CAppUI::gconf("dPplanningOp CSejour required_uf_soins") !== "obl") {
        $sejour->uf_soins_id = "";
    }

    if ($mode_entree_id = CAppUI::gconf("dPplanningOp CSejour mode_entree_dhe_urgences_lite")) {
        $sejour->mode_entree_id = $mode_entree_id;
    }
}

if (CModule::getActive("reservation") && !$sejour_id && $dialog) {
    $sejour->_date_entree_prevue = $sejour->_date_sortie_prevue = $date_reservation;
}

if (CModule::getActive("maternite")) {
    if ($grossesse_id) {
        $sejour->grossesse_id = $grossesse_id;
    }

    $sejour->loadRefGrossesse();

    if (!$sejour->_id && $grossesse_id) {
        $sejour->type_pec            = 'O';
        $sejour->_date_entree_prevue = $sejour->_ref_grossesse->terme_prevu;
        $duree_sejour                = CAppUI::gconf("maternite general duree_sejour");
        $sejour->_date_sortie_prevue = CMbDT::date("+ $duree_sejour days", $sejour->_date_entree_prevue);
        $sejour->_duree_prevue       = $duree_sejour;
        $sejour->type                = $duree_sejour > 0 ? "comp" : "ambu";
    }
}

$patient->loadRefsCorrespondantsPatient();

$patient->loadRefsFwd();
$patient->loadRefsCorrespondants();
$patient->updateBMRBHReStatus($sejour);
$patient->loadRefsPatientHandicaps();
$patient->_ref_medecin_traitant->getExercicePlaces();

$correspondantsMedicaux = [];
if ($patient->_ref_medecin_traitant->_id) {
    $correspondantsMedicaux["traitant"] = $patient->_ref_medecin_traitant;
}
foreach ($patient->_ref_medecins_correspondants as $correspondant) {
  $correspondant->loadRefMedecin()->getExercicePlaces();
  $correspondantsMedicaux["correspondants"][] = $correspondant->_ref_medecin;
}

$medecin_adresse_par = "";
$sejour->loadRefAdresseParPraticien();
if ($sejour->adresse_par_prat_id && ($sejour->adresse_par_prat_id != $patient->_ref_medecin_traitant->_id)) {
  $medecin_adresse_par = $sejour->_ref_adresse_par_prat;
  $medecin_adresse_par->getExercicePlaces();
}

// Heures & minutes
$group  = CGroups::loadCurrent();
$config = CAppUI::conf("dPplanningOp CSejour");

$heure_sortie_ambu   = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_ambu', $group);
$heure_sortie_autre  = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_autre', $group);
$heure_entree_veille = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_veille', $group);
$heure_entree_jour   = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_jour', $group);

$sejour->makeCancelAlerts();
$sejour->loadNDA();

// Chargement des etablissements externes
$etab               = new CEtabExterne();
$count_etab_externe = $etab->countList();

// Récupération de la liste des services
$where              = [];
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
$where["group_id"]  = $sejour->_id ? "= '$sejour->group_id'" : "= '$group->_id'";
$service            = new CService();
$services           = $service->loadList($where, "nom");

foreach ($services as $_service) {
    $_service->loadRefUFSoins();
}

// Chargement des prestations système standard
$prestations = CPrestation::loadCurrentList();

$sortie_sejour = CMbDT::dateTime();
if ($sejour->sortie_reelle) {
    $sortie_sejour = $sejour->sortie_reelle;
}

// Mise à disposition de lits
$affectation          = new CAffectation();
$where                = [];
$where["entree"]      = "<= '$sortie_sejour'";
$where["sortie"]      = ">= '$sortie_sejour'";
$where["function_id"] = "IS NOT NULL";
/** @var CAffectation[] $blocages_lit */
$blocages_lit = $affectation->loadList($where);

$where["function_id"] = "IS NULL";
foreach ($blocages_lit as $blocage) {
    $blocage->loadRefLit()->loadRefChambre()->loadRefService();
    $where["lit_id"] = "= '$blocage->lit_id'";
    if (!$sejour->_id && $affectation->loadObject($where)) {
        $affectation->loadRefSejour()->loadRefPatient();
        $blocage->_ref_lit->_view .= " indisponible jusqu'à " . CMbDT::transform($affectation->sortie, null, "%Hh%Mmin %d-%m-%Y");
        $blocage->_ref_lit->_view .= " (" . $affectation->_ref_sejour->_ref_patient->_view . ")";
    }
}

$list_mode_sortie = [];
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
    $list_mode_sortie = CModeSortieSejour::listModeSortie($sejour->group_id);
}

$protocole = new CProtocole();
if ($protocole_id) {
    $protocole->load($protocole_id);
    $protocole->loadRefChir();
}

$check_block_dhe_appfine = false;

if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($patient, CGroups::loadCurrent()->_id);
    $patient->loadRefStatusPatientUser();
}

CUniteFonctionnelle::getAlertesUFs($sejour);

$ext_patient = new CPatient();
if ($ext_patient_id && $ext_cabinet_id) {
    $ext_patient = CIdSante400::getMatch("CPatient", "ext_patient_id-$ext_cabinet_id", $ext_patient_id)->loadTargetObject();
}

if (!$ext_patient->_id) {
    $ext_patient->nom       = $ext_patient_nom;
    $ext_patient->prenom    = $ext_patient_prenom;
    $ext_patient->naissance = $ext_patient_naissance;
}

$flag_sejour_encours_futur = false;
if ($sejour->entree >= CMbDT::dateTime() || $sejour->sortie >= CMbDT::dateTime()) {
    $flag_sejour_encours_futur = true;
}

$value_medecin_traitant = '';
$value_medecin_traitant_id = '';

if ($sejour->_ref_patient->patient_id) {
    $value_medecin_traitant = $sejour->medecin_traitant_id ? $sejour->_ref_medecin_traitant->_view : $patient->_ref_medecin_traitant->_view;
    $value_medecin_traitant_id = $sejour->medecin_traitant_id ? $sejour->medecin_traitant_id : $patient->_ref_medecin_traitant->_id;
}

// Création du template
$smarty = new CSmartyDP("modules/dPplanningOp");

$smarty->assign("contextual_call", (bool)$contextual_call);
$smarty->assign("ext_cabinet_id", $ext_cabinet_id);
$smarty->assign("ext_patient_id", $ext_patient_id);
$smarty->assign("ext_patient", $ext_patient);

$smarty->assign("urgInstalled", CModule::getInstalled("dPurgences"));
$smarty->assign("heure_sortie_ambu", $heure_sortie_ambu);
$smarty->assign("heure_sortie_autre", $heure_sortie_autre);
$smarty->assign("heure_entree_veille", $heure_entree_veille);
$smarty->assign("heure_entree_jour", $heure_entree_jour);
$smarty->assign("value_medecin_traitant", $value_medecin_traitant);
$smarty->assign("value_medecin_traitant_id", $value_medecin_traitant_id);

$smarty->assign("sejour", $sejour);
$smarty->assign("flag_sejour_encours_futur", $flag_sejour_encours_futur);
$smarty->assign("op", new COperation());
$smarty->assign("praticien", $praticien);
$smarty->assign("patient", $patient);
$smarty->assign("grossesse", new CGrossesse());
$smarty->assign("patient_handicap_list", (new CPatientHandicap())->_specs["handicap"]->_list);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("cpi_list", CChargePriceIndicator::getList(null, $sejour->group_id));

$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("count_etab_externe", $count_etab_externe);
$smarty->assign("medecin_adresse_par", $medecin_adresse_par);

$smarty->assign("etablissements", $etablissements);
$smarty->assign("listServices", $services);

$smarty->assign("prestations", $prestations);

$smarty->assign("blocages_lit", $blocages_lit);

$smarty->assign("list_mode_sortie", $list_mode_sortie);

$smarty->assign("dialog", $dialog);
$smarty->assign("mutation", $mutation);
$smarty->assign("dhe_mater", $dhe_mater);
$smarty->assign('protocole', $protocole);
$smarty->assign("check_block_dhe_appfine", $check_block_dhe_appfine);

$smarty->assign("current_date", CMbDT::dateTime());

$smarty->display("vw_edit_sejour");
