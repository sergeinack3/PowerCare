<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admissions\AdmissionsService;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$date      = CView::get("date", "date default|now", true);

CView::checkin();

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);

$sejour = new CSejour();
$sejour->load($sejour_id);

// Chargenemt du praticien
$sejour->loadRefPraticien();

// Chargement du patient
$sejour->loadRefPatient(1)->loadIPP();

// Chargment du numéro de dossier
$sejour->loadNDA();

// Chargements des notes sur le séjour
$sejour->loadRefsNotes();

// Chargement des prestations
$sejour->countPrestationsSouhaitees();

// Chargement des appels
$sejour->loadRefsAppel('sortie');

// Chargement des interventions
$whereOperations = ["annulee" => "= '0'"];
$sejour->loadRefsOperations($whereOperations);
foreach ($sejour->_ref_operations as $operation) {
    $operation->loadRefsActes();
    $operation->loadRefPlageOp();
}

// Chargement des affectation
$sejour->loadRefsAffectations();

if (CModule::getActive("maternite")) {
    $sejour->_sejours_enfants_ids = CMbArray::pluck($sejour->loadRefsNaissances(), "sejour_enfant_id");
    $sejour->loadRefFirstAffectation()->loadRefParentAffectation()->loadRefSejour();
}

// Chargement des modes de sortie
$sejour->loadRefEtablissementTransfert();
$sejour->loadRefServiceMutation();

if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($sejour->_ref_patient, $sejour->group_id);
    CAppFineClient::loadIdex($sejour, $sejour->group_id);
    $sejour->_ref_patient->loadRefStatusPatientUser();
}

$smarty = new CSmartyDP();

$smarty->assign("_sejour", $sejour);
$smarty->assign("hier", $hier);
$smarty->assign("flag_contextual_icons", AdmissionsService::flagContextualIcons());
$smarty->assign("date", $date);
$smarty->assign("demain", $demain);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("date_demain", $date_demain);
$smarty->assign("date_actuelle", $date_actuelle);
$smarty->assign("prestations", CPrestation::loadCurrentList());
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients", CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));

$smarty->display("inc_vw_sortie_line.tpl");
