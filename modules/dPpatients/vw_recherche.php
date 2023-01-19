<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Droit sur les consultations
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$canCabinet = CModule::getCanDo("dPcabinet");

// Droit sur les interventions et séjours
$canPlanningOp = CModule::getCanDo("dPplanningOp");

$user = CUser::get();

// Récupération des critères de recherche
$antecedent_patient    = CValue::getOrSession("antecedent_patient");
$traitement_patient    = CValue::getOrSession("traitement_patient");
$diagnostic_patient    = CValue::getOrSession("diagnostic_patient");
$motif_consult         = CValue::getOrSession("motif_consult");
$remarque_consult      = CValue::getOrSession("remarque_consult");
$examen_consult        = CValue::getOrSession("examen_consult");
$traitement_consult    = CValue::getOrSession("traitement_consult");
$conclusion_consult    = CValue::getOrSession("conclusion_consult");
$type                  = CValue::getOrSession("type");
$convalescence_sejour  = CValue::getOrSession("convalescence_sejour");
$remarque_sejour       = CValue::getOrSession("remarque_sejour");
$materiel_intervention = CValue::getOrSession("materiel_intervention");
$examen_per_op         = CValue::getOrSession("examen_per_op");
$examen_intervention   = CValue::getOrSession("examen_intervention");
$remarque_intervention = CValue::getOrSession("remarque_intervention");
$libelle_intervention  = CValue::getOrSession("libelle_intervention");
$ccam_intervention     = CValue::getOrSession("ccam_intervention");

$recherche_consult      = CValue::getOrSession("recherche_consult", "or");
$recherche_sejour       = CValue::getOrSession("recherche_sejour", "or");
$recherche_intervention = CValue::getOrSession("recherche_intervention", "or");

$page_sejour         = CValue::get('page_sejour', 0);
$page_interv         = CValue::get('page_interv', 0);
$page_consult        = CValue::get('page_consult', 0);
$page_antecedent     = CValue::get('page_antecedent', 0);
$page_traitement     = CValue::get('page_traitement', 0);
$page_consult        = CValue::get('page_consult', 0);
$page_dossierMedical = CValue::get('page_dossierMedical', 0);

// Recherche sur les antecedents
$ant = new CAntecedent();

/** @var CAntecedent[] $antecedents */
$antecedents  = array();
$patients_ant = array();
$where_ant    = array();

$ljoin["dossier_medical"] = "dossier_medical.object_id = antecedent.antecedent_id";
if ($antecedent_patient) {
  $where_ant["rques"]        = "LIKE '%$antecedent_patient%'";
  $where_ant["object_class"] = " = 'CPatient'";
}
$order_ant         = "antecedent_id, rques";
$total_antecedents = null;
if ($where_ant) {
  $total_antecedents = $ant->countList($where_ant, null, $ljoin);
  $antecedents       = $ant->loadList($where_ant, $order_ant, "$page_antecedent, 30", null, $ljoin);
}

foreach ($antecedents as $key => $_antecedent) {
  // Chargement du dossier medical du patient pour chaque antecedent
  $_antecedent->loadRefDossierMedical();

  $_antecedent->_ref_dossier_medical->loadRefObject();
  $antecedents_[$key] = $_antecedent->_ref_dossier_medical->object_id;
  $_antecedent->loadRefsFwd();
}

// Recherche sur les traitements
$trait = new CTraitement();

/** @var CTraitement[] $traitements */
$traitements = array();

$patients_trait           = array();
$where_trait              = array();
$ljoin["dossier_medical"] = "dossier_medical.object_id = traitement.traitement_id";

if ($traitement_patient) {
  $where_trait["traitement"]   = "LIKE '%$traitement_patient%'";
  $where_trait["object_class"] = " ='CPatient'";
}
$order_trait       = "traitement_id, traitement";
$total_traitements = null;
if ($where_trait) {
  $total_traitements = $trait->countList($where_trait, null, $ljoin);
  $traitements       = $trait->loadList($where_trait, $order_trait, "$page_traitement, 30", null, $ljoin);
}

foreach ($traitements as $key => $_traitement) {
  $_traitement->loadRefDossierMedical();
  $_traitement->_ref_dossier_medical->loadRefObject();
  $traitements_[$key] = $_traitement->_ref_dossier_medical->object_id;
  $_traitement->loadRefsFwd();
}

// Recherche sur les diagnostics
/** @var CDossierMedical[] $dossiersMed */
$dossiersMed = array();
$where_diag  = array();

if ($diagnostic_patient) {
  $where_diag["codes_cim"]    = "LIKE '%$diagnostic_patient%'";
  $where_diag["object_class"] = " = 'CPatient'";
}
$order_diag = "object_id";

$dossierMedical        = new CDossierMedical();
$pat_diag              = new CPatient();
$total_dossierMedicals = null;
if ($where_diag) {
  $total_dossierMedicals = $dossierMedical->countList($where_diag);
  $dossiersMed           = $dossierMedical->loadList($where_diag, $order_diag, "$page_dossierMedical, 30");
}

foreach ($dossiersMed as $value) {
  $value->loadRefObject();
  $value->loadRefsFwd();
}

$where_motif      = null;
$where_remarque   = null;
$where_examen     = null;
$where_traitement = null;
$where_consult    = null;

// Recherche sur les Consultations
/** @var CConsultation[] $consultations */
$consultations   = array();
$consult         = new CConsultation();
$patient_consult = array();

if ($recherche_consult == "and") {
  if ($motif_consult) {
    $where_consult["motif"] = "LIKE '%$motif_consult%'";
  }
  if ($remarque_consult) {
    $where_consult["rques"] = "LIKE '%$remarque_consult%'";
  }
  if ($examen_consult) {
    $where_consult["examen"] = "LIKE '%$examen_consult%'";
  }
  if ($traitement_consult) {
    $where_consult["traitement"] = "LIKE '%$traitement_consult%'";
  }
  if ($conclusion_consult) {
    $where_consult["conclusion"] = "LIKE '%$conclusion_consult%'";
  }
}

if ($recherche_consult == "or") {
  if ($motif_consult) {
    $where_motif     = "`motif` LIKE '%$motif_consult%'";
    $where_consult[] = $where_motif;
  }
  if ($remarque_consult) {
    $where_remarque  = "`rques` LIKE '%$remarque_consult%'";
    $where_consult[] = $where_remarque;
  }
  if ($examen_consult) {
    $where_examen    = "`examen` LIKE '%$examen_consult%'";
    $where_consult[] = $where_examen;
  }
  if ($traitement_consult) {
    $where_traitement = "`traitement` LIKE '%$traitement_consult%'";
    $where_consult[]  = $where_traitement;
  }
  if ($where_consult) {
    $where_consult = implode(" OR ", $where_consult);
  }
}

$patients_consult = array();

$order_consult  = "patient_id";
$total_consults = null;
if ($where_consult) {
  $total_consults = $consult->countList($where_consult);
  $consultations  = $consult->loadList($where_consult, $order_consult, "$page_consult, 30");
}

foreach ($consultations as $value) {
  $value->loadRefPatient();
}

// Recherche sur les sejours
/** @var CSejour[] $sejours */
$sejours         = array();
$sejour          = new CSejour();
$patients_sejour = array();
$where_sejour    = null;

if ($recherche_sejour == "and") {
  if ($typeAdmission_sejour) {
    $where_sejour["type"] = "LIKE '%$typeAdmission_sejour%'";
  }
  if ($convalescence_sejour) {
    $where_sejour["convalescence"] = "LIKE '%$convalescence_sejour%'";
  }
  if ($remarque_sejour) {
    $where_sejour["rques"] = "LIKE '%$remarque_sejour%'";
  }
}

if ($recherche_sejour == "or") {
  if ($type) {
    $where_type     = "`type` LIKE '%$type%'";
    $where_sejour[] = $where_type;
  }
  if ($convalescence_sejour) {
    $where_convalescence = "`convalescence` LIKE '%$convalescence_sejour%'";
    $where_sejour[]      = $where_convalescence;
  }
  if ($remarque_sejour) {
    $where_remarque = "`rques` LIKE '%$remarque_sejour%'";
    $where_sejour[] = $where_remarque;
  }
  if ($where_sejour) {
    $where_sejour = implode(" OR ", $where_sejour);
  }
}

$order_sejour  = "patient_id";
$total_sejours = null;
if ($where_sejour) {
  $total_sejours = $sejour->countList($where_sejour);
  $sejours       = $sejour->loadList($where_sejour, $order_sejour, "$page_sejour, 30");
}

foreach ($sejours as $value) {
  $value->loadRefPatient();
}

// Recherches sur les Interventions
/** @var COperation[] $interventions */
$interventions         = array();
$intervention          = new COperation();
$patients_intervention = array();
$where_intervention    = null;

if ($recherche_intervention == "and") {
  if ($materiel_intervention) {
    $where_intervention["materiel"] = "LIKE '%$materiel_intervention%'";
  }
  if ($examen_per_op) {
    $where_intervention["exam_per_op"] = "LIKE '%$examen_per_op%'";
  }
  if ($examen_intervention) {
    $where_intervention["examen"] = "LIKE '%$examen_intervention%'";
  }
  if ($remarque_intervention) {
    $where_intervention["rques"] = "LIKE '%$remarque_intervention%'";
  }
  if ($libelle_intervention) {
    $where_intervention["libelle"] = "LIKE '%$libelle_intervention%'";
  }
  if ($ccam_intervention) {
    $where_intervention["codes_ccam"] = "LIKE '%$ccam_intervention%'";
  }
}

if ($recherche_intervention == "or") {
  if ($materiel_intervention) {
    $where_materiel       = "`materiel` LIKE '%$materiel_intervention%'";
    $where_intervention[] = $where_materiel;
  }
  if ($examen_per_op) {
    $where_intervention[] = "`exam_per_op` LIKE '%$examen_per_op%'";
  }
  if ($examen_intervention) {
    $where_examen         = "`examen` LIKE '%$examen_intervention%'";
    $where_intervention[] = $where_examen;
  }
  if ($remarque_intervention) {
    $where_remarque       = "`rques` LIKE '%$remarque_intervention%'";
    $where_intervention[] = $where_remarque;
  }
  if ($libelle_intervention) {
    $where_libelle        = "`libelle` LIKE '%$libelle_intervention%'";
    $where_intervention[] = $where_libelle;
  }
  if ($ccam_intervention) {
    $where_ccam           = "`codes_ccam` LIKE '%$ccam_intervention%'";
    $where_intervention[] = $where_ccam;
  }
  if ($where_intervention) {
    $where_intervention = implode(" OR ", $where_intervention);
  }
}

$order_intervention = "rques";
$total_intervs      = null;
if ($where_intervention) {
  $total_intervs = $intervention->countList($where_intervention);
  $interventions = $intervention->loadlist($where_intervention, $order_intervention, "$page_interv, 30");
}

foreach ($interventions as &$intervention) {
  $intervention->loadRefSejour();
  $intervention->_ref_sejour->loadRefPatient();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("dossierMedical", $dossierMedical);

$smarty->assign("canCabinet", $canCabinet);
$smarty->assign("canPlanningOp", $canPlanningOp);

$smarty->assign("sejours", $sejours);
$smarty->assign("page_sejour", $page_sejour);
$smarty->assign("total_sejours", $total_sejours);
$smarty->assign("interventions", $interventions);
$smarty->assign("page_interv", $page_interv);
$smarty->assign("total_intervs", $total_intervs);
$smarty->assign("consultations", $consultations);
$smarty->assign("page_consult", $page_consult);
$smarty->assign("total_consults", $total_consults);
$smarty->assign("antecedents", $antecedents);
$smarty->assign("page_antecedent", $page_antecedent);
$smarty->assign("total_antecedents", $total_antecedents);
$smarty->assign("traitements", $traitements);
$smarty->assign("page_traitement", $page_traitement);
$smarty->assign("total_traitements", $total_traitements);
$smarty->assign("dossiersMed", $dossiersMed);
$smarty->assign("page_dossierMedical", $page_dossierMedical);
$smarty->assign("total_dossierMedicals", $total_dossierMedicals);

$smarty->assign("ant", $ant);
$smarty->assign("trait", $trait);
$smarty->assign("intervention", $intervention);
$smarty->assign("consult", $consult);
$smarty->assign("sejour", $sejour);
$smarty->assign("pat_diag", $pat_diag);

$smarty->assign("antecedent_patient", $antecedent_patient);
$smarty->assign("traitement_patient", $traitement_patient);
$smarty->assign("diagnostic_patient", $diagnostic_patient);
$smarty->assign("motif_consult", $motif_consult);
$smarty->assign("remarque_consult", $remarque_consult);
$smarty->assign("examen_consult", $examen_consult);
$smarty->assign("traitement_consult", $traitement_consult);
$smarty->assign("conclusion_consult", $conclusion_consult);
$smarty->assign("type", $type);
$smarty->assign("convalescence_sejour", $convalescence_sejour);
$smarty->assign("remarque_sejour", $remarque_sejour);
$smarty->assign("materiel_intervention", $materiel_intervention);
$smarty->assign("examen_per_op", $examen_per_op);
$smarty->assign("examen_intervention", $examen_intervention);
$smarty->assign("remarque_intervention", $remarque_intervention);
$smarty->assign("libelle_intervention", $libelle_intervention);
$smarty->assign("ccam_intervention", $ccam_intervention);

$smarty->assign("recherche_consult", $recherche_consult);
$smarty->assign("recherche_sejour", $recherche_sejour);
$smarty->assign("recherche_intervention", $recherche_intervention);

$smarty->assign("patients_ant", $patients_ant);
$smarty->assign("patients_trait", $patients_trait);
$smarty->assign("patients_consult", $patients_consult);
$smarty->assign("patients_sejour", $patients_sejour);
$smarty->assign("patients_intervention", $patients_intervention);

$smarty->assign("user_id", $user->_id);

$smarty->display("vw_recherche.tpl");
