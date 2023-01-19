<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatientSignature;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();
$sejour_id       = CView::get("sejour_id", "ref class|CSejour");
$rpu_id          = CView::get("rpu_id", "ref class|CRPU", true);
$_responsable_id = CView::get("_responsable_id", "ref class|CMediusers");
$fragment        = CView::get("fragment", "str");

CView::checkin();

$group = CGroups::get();
$user  = CMediusers::get();

$rpu = new CRPU();
if ($rpu_id && !$rpu->load($rpu_id)) {
    global $m, $tab;
    CAppUI::setMsg("Ce RPU n'est pas ou plus disponible", UI_MSG_WARNING);
    CAppUI::redirect("m=$m&tab=$tab&rpu_id=0");
}

// Création d'un RPU pour un séjour existant
if ($sejour_id && !$rpu->_id) {
    $rpu            = new CRPU();
    $rpu->sejour_id = $sejour_id;
}

$sejour  = $rpu->loadRefSejour();
$sejour->loadRefEtablissementProvenance();
$patient = $sejour->loadRefPatient();
if ($patient->_id) {
    $patient->_homonyme = count($patient->getPhoning($sejour->entree));
}

CAccessMedicalData::logAccess($sejour);

if ($rpu->_id) {
    $patient->loadRefLatestConstantes(null, ['poids', 'taille', "clair_creatinine"]);
    $patient->loadRefDossierMedical();
    $patient->loadRefsNotes();
    if ($patient->_ref_dossier_medical->_id) {
        $patient->_ref_dossier_medical->canDo();
        $patient->_ref_dossier_medical->loadRefsAllergies();
        $patient->_ref_dossier_medical->loadRefsAntecedents();
        $patient->_ref_dossier_medical->countAntecedents();
        $patient->_ref_dossier_medical->countAllergies();
    }
    // Chargement de l'IPP ($_IPP)
    $patient->loadIPP();
    $patient->countINS();

    $sejour->loadPatientBanner();
}

$services         = [];
$listResponsables = [];
$nb_printers = 0;

if (CModule::getActive("maternite")) {
    $patient->loadLastGrossesse();
}

if (CModule::getActive("printing")) {
    // Chargement des imprimantes pour l'impression d'étiquettes
    $user_printers = CMediusers::get();
    $function      = $user_printers->loadRefFunction();
    $nb_printers   = $function->countBackRefs("printers");
}

$form_tabs = [];
if (CModule::getActive('forms')) {
    $objects = [
        ['tab_dossier_infirmier', $rpu],
    ];

    $form_tabs = CExClassEvent::getTabEvents($objects);
}

$isPrescriptionInstalled = CModule::getActive("dPprescription") && CPrescription::isMPMActive();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("group", $group);
if (CModule::getActive("dPprescription")) {
    $sejour->loadRefPrescriptionSejour()->loadLinesElementImportant();
}

$ecg_tabs = CFilesCategory::getEmergencyTabCategories($group);

$smarty->assign("userSel", $user);
$smarty->assign("_responsable_id", $_responsable_id);
$smarty->assign("rpu", $rpu);
$smarty->assign("sejour", $sejour);
$smarty->assign("patient", $patient);
$smarty->assign("traitement", new CTraitement());
$smarty->assign("antecedent", new CAntecedent());
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("fragment", $fragment);
/* Verification de l'existance de la base DRC (utilisée dans les antécédents */
$smarty->assign('drc', array_key_exists('drc', CAppUI::conf('db')));
$smarty->assign('cisp', array_key_exists('cisp', CAppUI::conf('db')));
$smarty->assign("nb_printers", $nb_printers);
$smarty->assign('form_tabs', $form_tabs);
$smarty->assign('ecg_tabs', $ecg_tabs);

if ($isPrescriptionInstalled) {
    $smarty->assign("line", new CPrescriptionLineMedicament());
}

$smarty->display("vw_aed_rpu");
