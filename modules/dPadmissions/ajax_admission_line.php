<?php
/**
 * @package Mediboard\Admissions
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
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMP;
use Ox\Mediboard\Admissions\AdmissionsService;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$date         = CView::get("date", "date", true);
$print_global = CView::get("print_global", "bool default|0");
$reloadLine   = CView::get("reloadLine", "bool default|0");
CView::checkin();

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);

$sejour = CSejour::findOrNew($sejour_id);

$sejour->loadRefEtablissementProvenance();
$sejour->loadRefAdresseParPraticien();
$sejour->loadRefPraticien()->loadRefFunction();
$sejour->getPassageBloc();
$patient = $sejour->loadRefPatient();

$patient->loadIPP();
$patient->countINS();
$patient->loadStateDMP();
$patient->loadRefsPatientHandicaps();

// Dossier médical
$dossier_medical = $patient->loadRefDossierMedical(false);

// Chargement du numéro de dossier
$sejour->loadNDA();

// Chargement des notes sur le séjourw
$sejour->loadRefsNotes();

// Chargement des modes d'entrée
$sejour->loadRefEtablissementProvenance();

// Chargement des appels
$sejour->loadRefsAppel('admission');

// Chargement de l'affectation
$affectation = $sejour->loadRefFirstAffectation();
$affectation->updateView();
if (CModule::getActive("maternite")) {
    $affectation->loadRefParentAffectation()->loadRefSejour();
}

// Chargement des interventions
$whereOperations = ["annulee" => "= '0'"];
$operations      = $sejour->loadRefsOperations($whereOperations);

// Chargement optimisée des prestations
CSejour::massCountPrestationSouhaitees([$sejour]);
CSejour::massLoadPrestationSouhaitees(["$sejour_id" => $sejour]);

foreach ($operations as $operation) {
    $operation->loadRefsActes();
    $dossier_anesth = $operation->loadRefsConsultAnesth();
    $consultation   = $dossier_anesth->loadRefConsultation();
    $consultation->loadRefPlageConsult();
    $consultation->loadRefPraticien()->loadRefFunction();
    $dossier_anesth->_date_consult = $consultation->_date;
    $operation->loadRefPlageOp();
}

if (CAppUI::gconf("dPadmissions General show_deficience")) {
    CDossierMedical::massCountAntecedentsByType([$dossier_medical], "deficience");
}

$list_mode_entree = [];
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
    $mode_entree      = new CModeEntreeSejour();
    $where            = [
        "actif" => "= '1'",
    ];
    $list_mode_entree = $mode_entree->loadGroupList($where);
}

if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($sejour->_ref_patient, $sejour->group_id);
    CAppFineClient::loadIdex($sejour, $sejour->group_id);
    $sejour->_ref_patient->loadRefStatusPatientUser();
    $sejour->loadRefFolderLiaison("pread");
}

$smarty = new CSmartyDP();

$smarty->assign("_sejour", $sejour);
$smarty->assign("date_min", $date_min);
$smarty->assign("flag_dmp", AdmissionsService::flagDMP());
$smarty->assign("flag_contextual_icons", AdmissionsService::flagContextualIcons());
$smarty->assign("print_global", $print_global);
$smarty->assign("date_max", $date_max);
$smarty->assign("date_actuelle", $date_actuelle);
$smarty->assign("date_demain", $date_demain);
$smarty->assign("list_mode_entree", $list_mode_entree);
$smarty->assign("prestations", CPrestation::loadCurrentList());
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients", CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("single_line", true); // Indique qu'on charge la ligne seulement
$smarty->assign('reloadLine', $reloadLine);

$smarty->display("inc_vw_admission_line.tpl");
