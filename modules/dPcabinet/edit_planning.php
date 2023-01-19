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
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\CViewHistory;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;
use Ox\Mediboard\Cabinet\CReservation;
use Ox\Mediboard\Cabinet\CRessourceCab;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

// Recuperation de l'id de la consultation du passage en urgence
$consult_urgence_id   = CView::getRefCheckEdit("consult_urgence_id", "ref class|CConsultation");
$chir_id              = CView::getRefCheckEdit("chir_id", "ref class|CMediusers", CAppUI::gconf("dPcabinet PriseRDV keepchir") == 1);
$pat_id               = CView::get("pat_id", "ref class|CPatient");
$modal                = CView::get("modal", "bool default|0");
$callback             = CView::get("callback", "str");
$consultation_id      = CView::getRefCheckEdit("consultation_id", "ref class|CConsultation", true);
$plageconsult_id      = CView::get("plageconsult_id", "ref class|CPlageconsult");
$line_element_id      = CView::get("line_element_id", "ref class|CPrescriptionLineElement");
$sejour_id            = CView::get("sejour_id", "ref class|CSejour");
$date_planning        = CView::get("date_planning", "date");
$heure                = CView::get("heure", "time");
$grossesse_id         = CView::get("grossesse_id", "ref class|CGrossesse");
$multi_ressources     = CView::get("multi_ressources", "bool default|0");
$prats_ids            = CView::get("prats_ids", "str");
$prats_unselected_ids = CView::get("prats_unselected_ids", "str");
$ressources_ids       = CView::get("ressources_ids", "str");
$function_id          = CView::getRefCheckEdit("function_id", "ref class|CFunctions", true);
$prats_selected       = CValue::sessionAbs("planning_prats_selected");

CView::checkin();

$user = CUser::get();

$group = CGroups::loadCurrent();

$consult      = new CConsultation();
$chir         = new CMediusers();
$pat          = new CPatient();
$plageConsult = new CPlageconsult();

$ds = CSQLDataSource::get('std');

// L'utilisateur est-il praticien?
$mediuser = CMediusers::get();
if ($mediuser->isMedical()) {
  $chir = $mediuser;
}

// Vérification des droits sur les praticiens et les fonctions
$listPraticiens = CConsultation::loadPraticiens(PERM_EDIT);
if (!$consultation_id) {
  $prefs = CPreferences::getAllPrefsUsers($listPraticiens);
  foreach ($listPraticiens as $_prat) {
    if (isset($prefs[$_prat->user_id]["allowed_new_consultation"]) == 0) {
      unset($listPraticiens[$_prat->_id]);
    }
  }
}

CConsultation::guessUfMedicaleMandatory($listPraticiens);

$function      = new CFunctions();
$listFunctions = $function->loadSpecialites(PERM_EDIT);

$correspondantsMedicaux = [];
$medecin_adresse_par    = "";
$_function_id           = null;
$nb_plages              = 0;
$count_next_plage       = 0;

// Nouvelle consultation
if (!$consultation_id) {

  if ($plageconsult_id) {
    // On a fourni une plage de consultation
    $plageConsult->load($plageconsult_id);
  }
  else {
    if ($chir_id) {
      $chir = CMediusers::get($chir_id);
    }
  }

  // assign patient if defined in get
  if ($pat_id) {
    // On a fourni l'id du patient
    $pat->load($pat_id);
  }
  if ($sejour_id) {
    $consult->sejour_id = $sejour_id;
    $sejour             = new CSejour();
    $sejour->load($sejour_id);

    CAccessMedicalData::logAccess($sejour);

    $pat = $sejour->loadRefPatient();
  }
  if ($date_planning) {
    // On a fourni une date
    $consult->_date = $date_planning;
  }
  if ($heure) {
    // On a fourni une heure
    $consult->heure           = $heure;
    $consult->plageconsult_id = $plageconsult_id;
    $chir->load($plageConsult->chir_id);
  }

  // grossesse
  if (!$consult->grossesse_id && $grossesse_id) {
    $consult->grossesse_id = $grossesse_id;
  }
  if (CModule::getActive("maternite")) {
    $grossesse = $consult->loadRefGrossesse();
    if (!$consult->patient_id) {
      $consult->patient_id = $grossesse->parturiente_id;
    }
  }

  if ($line_element_id) {
    // RDV issu d'une ligne d'élément
    $consult->sejour_id = $sejour_id;

    $line = new CPrescriptionLineElement();
    $line->load($line_element_id);
    $func_cats    = $line->_ref_element_prescription->_ref_category_prescription->loadBackRefs("functions_category", null, "1");
    $func_categ   = reset($func_cats);
    $plageconsult = new CPlageconsult();

    $where = $ljoin = [];

    $where["pour_tiers"] = "= '1'";
    $where["date"]       = "BETWEEN '" . CMbDT::date() . "' AND '" . CMbDT::date("+3 month") . "'";

    if ($func_categ) {
      $_function_id                         = $func_categ->function_id;
      $where["users_mediboard.function_id"] = "= '$_function_id'";
      $ljoin["users_mediboard"]             = "users_mediboard.user_id = plageconsult.chir_id";
    }
    $nb_plages = $plageconsult->countList($where, null, $ljoin);
  }
}
else {
  // Consultation existante
  $reserved_ressources = [];
  $consult->load($consultation_id);

  CAccessMedicalData::logAccess($consult);

  $canConsult = $consult->canDo();

  $canConsult->needsRead("consultation_id");

  $consult->loadRefConsultAnesth();
  $consult->loadRefsNotes();
  $consult->loadRefSejour();
  $consult->loadRefPlageConsult()->loadRefs();
  $consult->loadRefReservedRessources();
  CStoredObject::massLoadFwdRef($consult->_ref_reserved_ressources, "plage_ressource_cab_id");
  foreach ($consult->_ref_reserved_ressources as $_reserved_ressource) {
    $reserved_ressources[] = $_reserved_ressource->loadRefPlageRessource()->loadRefRessource();
  }

  $chir = $consult->loadRefPraticien();

  $pat = $consult->loadRefPatient();
  $pat->loadIdVitale();

  // grossesse
  if (CModule::getActive("maternite")) {
    $consult->loadRefGrossesse();
  }

  $sejour                           = new CSejour();
  $whereSejour                      = [];
  $whereSejour["type"]              = "!= 'consult'";
  $whereSejour[]                    = "'$consult->_date' BETWEEN DATE(entree) AND DATE(sortie)";
  $whereSejour["patient_id"]        = "= '$consult->patient_id'";
  $whereSejour["group_id"]          = "= '$group->_id'";
  $consult->_count_matching_sejours = $sejour->countList($whereSejour);

  //next consultation ?
  $dateW                 = $consult->_ref_plageconsult->date;
  $where                 = [];
  $whereN["patient_id"]  = " = '$consult->patient_id'";
  $whereN["date"]        = " >= '$dateW'";
  $ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
  $count_next_plage      = $consult->countList($whereN, null, $ljoin);
}

// Correspondants médicaux
$correspondants = $pat->loadRefsCorrespondants();
foreach ($correspondants as $_correspondant) {
    $correspondantsMedicaux["correspondants"][] = $_correspondant->_ref_medecin;
}

if ($pat->_ref_medecin_traitant->_id) {
    $correspondantsMedicaux["traitant"] = $pat->_ref_medecin_traitant;
}

$consult->loadRefAdresseParPraticien();
if ($consult->adresse_par_prat_id && ($consult->adresse_par_prat_id != $pat->_ref_medecin_traitant->_id)) {
    $consult->_ref_adresse_par_prat->getExercicePlaces();
    $medecin_adresse_par = $consult->_ref_adresse_par_prat;
}

if (!$modal) {
  // Save history
  $params = [
    "consult_urgence_id" => $consult_urgence_id,
    "consultation_id"    => $consultation_id,
    "plageconsult_id"    => $plageconsult_id,
    "sejour_id"          => $sejour_id,
    "date_planning"      => $date_planning,
    "grossesse_id"       => $grossesse_id,
  ];

  $object = null;
  $type   = CViewHistory::TYPE_VIEW;

  if ($consultation_id) {
    $object = $consult;
    $type   = CViewHistory::TYPE_EDIT;
  }
  elseif ($plageconsult_id) {
    $object = new CPlageconsult();
    $object->load($plageconsult_id);
    $type = CViewHistory::TYPE_NEW;
  }
  else {
    $object = $chir;
  }

  CViewHistory::save($object, $type, $params);
}

// Chargement des categories
$categorie        = new CConsultationCategorie();
$whereCategorie[] = "`function_id` = '$chir->function_id' OR `praticien_id` = '$chir->_id'";
$orderCategorie   = "nom_categorie ASC";
/** @var CConsultationCategorie[] $categories */
$categories = $categorie->loadList($whereCategorie, $orderCategorie);

// Creation du tableau de categories simplifié pour le traitement en JSON
$listCat = [];
foreach ($categories as $_categorie) {
  $listCat[$_categorie->_id] = [
    "nom_icone"    => $_categorie->nom_icone,
    "duree"        => $_categorie->duree,
    "commentaire"  => $_categorie->commentaire,
    "seance"       => $_categorie->seance,
    "max_seances"  => $_categorie->max_seances,
    "anticipation" => $_categorie->anticipation,
    "nb_consult"   => $_categorie->countRefConsultations($pat->_id)];
}

$cerfa_entente_prealable = 0;
if ($consult->_id && $consult->categorie_id) {
  $categorie = $consult->loadRefCategorie();

  if ($categorie->seance) {
    $cerfa_entente_prealable = $categorie->isCerfaEntentePrealable($consult->patient_id);
  }
}

// Ajout du motif de la consultation passé en parametre
if (!$consult->_id && $consult_urgence_id) {
  // Chargement de la consultation de passage aux urgences
  $consultUrgence = new CConsultation();
  $consultUrgence->load($consult_urgence_id);
  $consultUrgence->loadRefSejour();
  $consultUrgence->_ref_sejour->loadRefRPU();
  $consult->motif = "Reconvocation suite à un passage aux urgences, {$consultUrgence->_ref_sejour->_ref_rpu->motif}";
}

// Locks sur le rendez-vous
$consult->_locks = null;
$today           = CMbDT::date();
if ($consult->_id) {
  if ($consult->_datetime < $today && !CAppUI::gconf("dPcabinet CConsultation cancel_rdv")) {
    $consult->_locks[] = "datetime";
  }

  if ($consult->chrono == CConsultation::TERMINE && !$consult->annule) {
    $consult->_locks[] = "termine";
  }

  if ($consult->valide) {
    $consult->_locks[] = "valide";
  }
}

$_functions = [];

if ($chir->_id) {
  $chir->loadRefFunction()->loadRefGroup();
  $_functions = $chir->loadBackRefs("secondary_functions");

  foreach ($_functions as $_function) {
    $_function->_ref_function->loadRefGroup();
  }
}

// Consultation suivantes, en cas de suppression ou annulation
$following_consultations = [];
if ($pat->_id) {
  $from_date               = CAppUI::pref("today_ref_consult_multiple") ? CMbDT::date() : $consult->_date;
  $where["date"]           = ">= '$from_date'";
  $where["chrono"]         = "< '48'";
  $where["annule"]         = "= '0'";
  $following_consultations = $pat->loadRefsConsultations($where);
  unset($following_consultations[$consult->_id]);                   //removing the targeted consultation
  foreach ($following_consultations as $_consultation) {
    $_consultation->loadRefPraticien()->loadRefFunction();
    $_consultation->canEdit();
    $pat->updateBMRBHReStatus($_consultation);
  }
}

// Affichage de l'autocomplete des éléments de prescription
$display_elt = false;

if (CModule::getActive("dPprescription")) {
  $consult->loadRefElementPrescription();

  $elt               = new CElementPrescription();
  $elt->consultation = 1;
  if ($elt->countMatchingList()) {
    $display_elt = true;

    if ($line_element_id) {
      $display_elt = false;
    }
    elseif ($consult->_id) {
      $task = $consult->loadRefTask();
      if ($task->_id && $task->prescription_line_element_id) {
        $display_elt = false;
      }
    }
  }
}

$consult->loadPosition();

// Vérification de l'existence de formulaire automatiques
$ex_class_events = (!$consult->_id && CModule::getActive('forms'))
  ? CExClassEvent::countForClass('CConsultation', 'prise_rdv_auto', 'required')
  : null;

$prats                  = [];
$prats_unselected       = [];
$ressources             = [];
$plage_ressource_id     = null;
$unavailable_ressources = null;

if (!$consult->_id) {
  if (is_countable($prats_ids) && count($prats_ids)) {
    $prat = new CMediusers();

    $where = [
      "user_id" => CSQLDataSource::prepareIn($prats_ids),
    ];

    $prats = $prat->loadList($where);

    CStoredObject::massLoadFwdRef($prats, "function_id");

    foreach ($prats as $_prat) {
      $_prat->loadRefFunction();
    }

    // Recherche de la plage avec la granularité la plus élevée
    $plage = new CPlageconsult();
    $where = [
      "date"    => "= '$plageConsult->date'",
      "'$heure' BETWEEN debut AND fin",
      "chir_id" => CSQLDataSource::prepareIn($prats_ids),
    ];

    $plage->loadObject($where, "freq DESC");

    if ($plage->_id) {
      $plageConsult = $plage;
    }
  }

  if (is_countable($prats_unselected_ids) && count($prats_unselected_ids)) {
    $prat_unselected = new CMediusers();

    $where = [
      "user_id" => CSQLDataSource::prepareIn($prats_unselected_ids),
    ];

    $prats_unselected = $prat_unselected->loadGroupList($where);

    CStoredObject::massLoadFwdRef($prats_unselected, "function_id");

    foreach ($prats_unselected as $_prat) {
      $_prat->loadRefFunction();
    }
  }
}

$date = ($date_planning) ? $date_planning : $consult->_date;
if (!$heure && $consult->heure) {
  $heure = $consult->heure;
}

// Get all active resources
$ressource = new CRessourceCab();

$ljoin = [
  "plage_ressource_cab" => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id",
  "reservation"         => "reservation.plage_ressource_cab_id = plage_ressource_cab.plage_ressource_cab_id",
];
$where = [
  "ressource_cab.actif"       => "= '1'",
  "plage_ressource_cab.date"  => "= '$date'",
  "plage_ressource_cab.debut" => "< '$heure'",
  "plage_ressource_cab.fin"   => "> '$heure'",
];

if ($function_id) {
  $where["function_id"] = "= '$function_id'";
}

$ressources = $ressource->loadList($where, null, null, null, $ljoin);

// Get inactive resources linked to the appointment (to delete it for example)
$inactive_reserved_resources = [];
if ($consult->_id) {
  $ljoin                       = [
    "plage_ressource_cab" => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id",
    "reservation"         => "reservation.plage_ressource_cab_id = plage_ressource_cab.plage_ressource_cab_id",
  ];
  $where                       = [
    "ressource_cab.actif"    => "= '0'",
    "reservation.patient_id" => $ds->prepare("= ?", $consult->patient_id),
    "reservation.date"       => $ds->prepare("= ?", $date),
    "reservation.heure"      => $ds->prepare("= ?", $heure),
  ];
  $inactive_reserved_resources = $ressource->loadList($where, null, null, null, $ljoin);
}

$ressources += $inactive_reserved_resources;

$ljoin = ["plage_ressource_cab" => "plage_ressource_cab.plage_ressource_cab_id = reservation.plage_ressource_cab_id",
          "ressource_cab"       => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id"];

$reservation = new CReservation();
$where       = [
  "reservation.date"  => $ds->prepare("= ?", $date),
  "reservation.heure" => $ds->prepare("= ?", $heure),
];

$reservations = $reservation->loadList($where, null, null, null, $ljoin);
CStoredObject::massLoadFwdRef($reservations, "plage_ressource_cab_id");

$patients_ids_resources = [];
foreach ($reservations as $_reservation) {
  if (!isset($patients_ids_resources[$_reservation->patient_id])) {
    $patients_ids_resources[$_reservation->patient_id] = [];
  }

  $patients_ids_resources[$_reservation->patient_id][] = $_reservation->loadRefPlageRessource()->ressource_cab_id;
}

$unavailable_ressources = [];

$time = new DateTime($heure);
// Go through ressources/reservation to find unavailable ressources
foreach ($ressources as $_ressource) {
  foreach ($reservations as $_reservation) {
    $_reservation->loadRefPlageRessource();
    if ($_reservation->_ref_plage_ressource->ressource_cab_id === $_ressource->_id) {
      $res_time = new DateTime($_reservation->heure);

      // Reduce the amount of queries by checking if the res time is lower than the time
      if ($time >= $res_time) {
        $max = clone $res_time;

        $str_freq      = new DateTime($_reservation->_ref_plage_ressource->freq);
        $freq_interval = new DateInterval("PT" . $str_freq->format('H') . "H" . $str_freq->format('i') . "M");
        for ($i = 0; $i < $_reservation->duree; $i++) {
          $max->add($freq_interval);
        }

        if ($time >= $res_time && $time < $max) {
          $unavailable_ressources[] = $_ressource;
        }
      }
    }
  }
}

// Si on trouve une granularité supérieure dans les plages de ressources,
// on la passe au formulaire
$plage = new CPlageRessourceCab();
$where = [
  "date"             => "= '$plageConsult->date'",
  "'$heure' BETWEEN debut AND fin",
  "ressource_cab_id" => CSQLDataSource::prepareIn($ressources_ids),
];

$plage->loadObject($where, "freq DESC");

if ($plage->_id && $plage->freq > $plageConsult->freq) {
  $plage_ressource_id = $plage->_id;
  $plageConsult->freq = $plage->freq;
  $plageConsult->updateFormFields();
}


// Is the appointment a meeting
if ($consult->reunion_id) {
  $consult->loadRefReunion();
}

$create_sejour_consult = false;
if ($consult->patient_id && !$consult->sejour_id && (!$consult->_id || $consult->_force_create_sejour)
  && ($consult->_ref_praticien->_ref_function->create_sejour_consult || $consult->_create_sejour_activite_mixte)
) {
  $create_sejour_consult = true;
}

//On détermine si la consultation est issue d'une plage synchronisée
$agenda_praticien   = $consult->loadRefPlageConsult()->loadRefAgendaPraticien();
$plage_synchronized = false;

if ($agenda_praticien->_id) {
  $plage_synchronized = $agenda_praticien->sync;
}

$allow_teleconsultation = CModule::getActive('teleconsultation') ? CAppUI::loadPref('tamm_allow_teleconsultation', $chir->_id) : false;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCat", $listCat);
$smarty->assign("categories", $categories);
$smarty->assign("plageConsult", $plageConsult);
$smarty->assign("plage_synchronized", $plage_synchronized);
$smarty->assign("consult", $consult);
$smarty->assign("following_consultations", $following_consultations);
$smarty->assign("today_ref_multiple", CAppUI::pref("today_ref_consult_multiple"));
$smarty->assign("chir", $chir);
$smarty->assign("_functions", $_functions);
$smarty->assign("pat", $pat);
$smarty->assign("listPraticiens", $listPraticiens);
$smarty->assign("listFunctions", $listFunctions);
$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("medecin_adresse_par", $medecin_adresse_par);
$smarty->assign("today", $today);
$smarty->assign("date_planning", $date_planning);
$smarty->assign("_function_id", $_function_id);
$smarty->assign("line_element_id", $line_element_id);
$smarty->assign("nb_plages", $nb_plages);
$smarty->assign("modal", $modal);
$smarty->assign("callback", $callback);
$smarty->assign("next_consult", $count_next_plage);
$smarty->assign("display_elt", $display_elt);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs());
$smarty->assign("ex_class_events", $ex_class_events);
$smarty->assign("cerfa_entente_prealable", $cerfa_entente_prealable);
$smarty->assign("multi_ressources", $multi_ressources);
$smarty->assign("prats", $prats);
$smarty->assign("selected_practitioners", $prats_selected);
$smarty->assign("prats_unselected", $prats_unselected);
$smarty->assign("resources", $ressources);
$smarty->assign("unavailable_resources", $unavailable_ressources);
$smarty->assign("selected_resources", $ressources_ids);
$smarty->assign("patients_ids_resources", $patients_ids_resources);
$smarty->assign("plage_ressource_id", $plage_ressource_id);
$smarty->assign("create_sejour_consult", $create_sejour_consult);
$smarty->assign("isCabinet", CAppUI::isCabinet() || CAppUI::isGroup());
$smarty->assign("all_prats", array_merge($prats, $prats_unselected));
$smarty->assign("allow_teleconsultation", $allow_teleconsultation);

if ($consult->reunion_id || $consult->_id) {
  $smarty->assign("reserved_ressources", $reserved_ressources);
}

$smarty->display("edit_planning.tpl");
