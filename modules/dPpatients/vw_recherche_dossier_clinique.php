<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSearchCriteria;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Patients\CTypeEvenementPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

$date = CMbDT::date();

$mod_tamm           = CView::get("module_tamm", "bool default|0");
$user_id            = CView::get("user_id", "ref class|CUser default|" . CAppUI::$user->_id);
$function_id        = CView::get("function_id", "ref class|CFunctions");
$year_stats         = CView::get("year_stats", "num default|" . CMbDT::format($date, "%Y"));
$search_criteria_id = CView::get("search_criteria_id", "ref class|CSearchCriteria");

$date_min = "$year_stats-01-01 00:00:00";
$date_max = "$year_stats-12-31 23:59:59";

$criteria = new CSearchCriteria();
$criteria->load($search_criteria_id);

if (!$criteria->_id) {
  $criteria->owner_id = $user_id;
}

if (!isset($_SESSION["dPpatients"])) {
  $_SESSION["dPpatients"] = array();
}

// save form info
$patient = new CPatient();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $patient);
$patient->_id = "";
$patient->loadRefsFwd();

$consult = new CConsultation();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $consult);
$consult->loadRefsFwd();
$consult->_rques_consult  = CView::get("_rques_consult", "str default|" . CValue::session("_rques_consult"));
$consult->_examen_consult = CView::get("_examen_consult", "str default|" . CValue::session("_examen_consult"));

$sejour = new CSejour();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $sejour);
$sejour->loadRefsFwd();
$sejour->_rques_sejour = CView::get("_rques_sejour", "str default|" . CValue::session("_rques_sejour"));

$interv = new COperation();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $interv);
$interv->loadRefsFwd();
$interv->_libelle_interv = CView::get("_libelle_interv", "str default|" . CValue::session("_libelle_interv"));
$interv->_rques_interv   = CView::get("_rques_interv", "str default|" . CValue::session("_rques_interv"));

$antecedent = new CAntecedent();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $antecedent);
$antecedent->loadRefsFwd();

$traitement = new CTraitement();
CMbObject::setProperties($_GET + $_SESSION["dPpatients"], $traitement);
$traitement->loadRefsFwd();

$prescription       = new CPrescription();
$prescription->type = CView::get("type_prescription", "", true);

if (CPrescription::isMPMActive()) {
    $line_med            = new CPrescriptionLineMedicament();
    $line_med->code_ucd  = CView::get("code_ucd", "str", true);
    $line_med->code_cis  = CView::get("code_cis", "str", true);
    $line_med->_ucd_view = CView::get("produit", "str", true);
}

$libelle_produit = CView::get("libelle_produit", "str", true);

$classes_atc  = CView::get("classes_atc", "str", true);
$keywords_atc = CView::get("keywords_atc", "str", true);

$composant          = CView::get("composant", "str", true);
$keywords_composant = CView::get("keywords_composant", "str", true);

$indication          = CView::get("indication", "str", true);
$type_indication     = CView::get("type_indication", "str", true);
$keywords_indication = CView::get("keywords_indication", "str", true);

$commentaire = CView::get("commentaire", "str", true);
CView::checkin();

$user = new CMediusers();
$user->load($user_id);

$users_list = array();

if (!CAppUI::$user->isPraticien()) {
  $users_list = $user->loadPraticiens(PERM_READ);
}

$functions_list = CMediusers::loadFonctions();

$type_event  = new CTypeEvenementPatient();
$type_events = $type_event->loadList(null, "libelle ASC");

//liste des critères de recherche
$where_criteria   = array();
$where_criteria[] = "owner_id = '$user_id'";

$search_criteria = new CSearchCriteria();
$list_criteria   = $search_criteria->loadList($where_criteria);

// filter dates
$sejour->_date_min = $date_min;
$sejour->_date_max = $date_max;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("users_list", $users_list);
$smarty->assign("user_id", $user_id);
$smarty->assign("function_id", $function_id);
$smarty->assign("functions_list", $functions_list);
$smarty->assign("patient", $patient);
$smarty->assign("antecedent", $antecedent);
$smarty->assign("traitement", $traitement);
$smarty->assign("consult", $consult);
$smarty->assign("sejour", $sejour);
$smarty->assign("interv", $interv);
$smarty->assign("prescription", $prescription);
if (CPrescription::isMPMActive()) {
    $smarty->assign("line_med", $line_med);
}
$smarty->assign("libelle_produit", $libelle_produit);
$smarty->assign("classes_atc", $classes_atc);
$smarty->assign("keywords_atc", $classes_atc);
$smarty->assign("composant", $composant);
$smarty->assign("keywords_composant", $keywords_composant);
$smarty->assign("indication", $indication);
$smarty->assign("keywords_indication", $keywords_indication);
$smarty->assign("type_indication", $type_indication);
$smarty->assign("commentaire", $commentaire);
$smarty->assign("mod_tamm", $mod_tamm);
$smarty->assign("year_stats", $year_stats);
$smarty->assign("criteria", $criteria);
$smarty->assign("user", $user);
$smarty->assign("type_events", $type_events);
$smarty->assign("list_criteria", $list_criteria);

$smarty->display("vw_recherche_dossier_clinique.tpl");
