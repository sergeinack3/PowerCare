<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

// PAS DE PASSAGE AU CVIEW CAR SOUCI DE SESSION !!!

$services_ids       = CValue::getOrSession("services_ids");
$triAdm             = CValue::getOrSession("triAdm");
$_type_admission    = CValue::getOrSession("_type_admission", "ambucomp");
$filter_function    = CValue::getOrSession("filter_function");
$date               = CValue::getOrSession("date");
$date_min           = CValue::getOrSession("date_min");
$granularite        = CValue::getOrSession("granularite");
$readonly           = CValue::getOrSession("readonly", 0);
$duree_uscpo        = CValue::getOrSession("duree_uscpo", "0");
$isolement          = CValue::getOrSession("isolement", "0");
$prestation_id      = CValue::getOrSession("prestation_id", CAppUI::pref("prestation_id_hospi"));
$item_prestation_id = CValue::getOrSession("item_prestation_id");

$group_id = CGroups::loadCurrent()->_id;

if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "standard") {
    CValue::setSession("prestation_id", "");
    $prestation_id = "";
}


if (is_array($services_ids)) {
    CMbArray::removeValue("", $services_ids);
    CMbArray::removeValue("NP", $services_ids);
}

$where   = [
    "annule"            => "= '0'",
    "sejour.group_id"   => "= '$group_id'",
    "sejour.service_id" => "IS NULL " . (is_array($services_ids) && count($services_ids) ?
            "OR `sejour`.`service_id` " . CSQLDataSource::prepareIn($services_ids) : ""),
];
$where[] = "(sejour.type != 'seances' && affectation.affectation_id IS NULL) || sejour.type = 'seances'";

$order = null;
switch ($triAdm) {
    case "date_entree":
        $order = "entree ASC, sortie ASC";
        break;

    case "praticien":
        $order = "users_mediboard.function_id, users.user_last_name, users.user_first_name";
        break;

    case "patient":
        $order = "patients.nom, patients.prenom";
        break;

    default:
        $order = "users_mediboard.function_id, sejour.entree_prevue, patients.nom, patients.prenom";
}

switch ($_type_admission) {
    case "ambucomp":
        $where["sejour.type"] = "IN ('ambu', 'comp')";
        break;
    case 'ambucompssr':
        $where["sejour.type"] = "IN ('ambu', 'comp', 'ssr')";
        break;
    case "0":
        break;
    default:
        $where["sejour.type"] = "= '$_type_admission'";
}

$sejour = new CSejour();
$ljoin  = [
    "affectation"     => "sejour.sejour_id = affectation.sejour_id",
    "users_mediboard" => "sejour.praticien_id = users_mediboard.user_id",
    "users"           => "users_mediboard.user_id = users.user_id",
    "patients"        => "sejour.patient_id = patients.patient_id",
];

$period   = "";
$nb_unite = 0;

switch ($granularite) {
    case "day":
        $service_id = count($services_ids) == 1 ? reset($services_ids) : "";

        $tab_hour_debut = [];
        $tab_hour_fin   = [];
        foreach ($services_ids as $_service_id) {
            $tab_hour_debut[] = CAppUI::conf("dPhospi vue_temporelle hour_debut_day", "CService-$_service_id");
            $tab_hour_fin[]   = CAppUI::conf("dPhospi vue_temporelle hour_fin_day", "CService-$_service_id");
        }

        $hour_debut = min($tab_hour_debut);
        $hour_fin   = max($tab_hour_fin);

        // Inversion si l'heure de début est supérieure à celle de fin
        if ($hour_debut > $hour_fin) {
            [$hour_debut, $hour_fin] = [$hour_fin, $hour_debut];
        }

        $period   = "1hour";
        $unite    = "hour";
        $nb_unite = 1;
        $nb_ticks = $hour_fin - $hour_debut + 1;
        // Réinitilisation de date_min si changement de date
        if (!$date_min) {
            $date_min = CMbDT::date($date) . " " . str_pad($hour_debut, 2, "0", STR_PAD_LEFT) . ":00:00";
        }
        break;
    case "48hours":
        $unite            = "hour";
        $nb_unite         = 2;
        $nb_ticks         = 24;
        $step             = "+2 hours";
        $period           = "2hours";
        $date_min         = CMbDT::dateTime(CMbDT::date($date));
        break;
    case "72hours":
        $unite            = "hour";
        $nb_unite         = 3;
        $nb_ticks         = 24;
        $step             = "+3 hours";
        $period           = "3hours";
        $date_min         = CMbDT::dateTime(CMbDT::date($date));
        break;
    case "week":
        $period   = "6hours";
        $unite    = "hour";
        $nb_unite = 6;
        $nb_ticks = 28;
        $date_min = CMbDT::dateTime("-2 days", CMbDT::date($date));
        break;

    case "4weeks":
        $period   = "1day";
        $unite    = "day";
        $nb_unite = 1;
        $nb_ticks = 28;
        $date_min = CMbDT::dateTime("-1 week", CMbDT::dirac("week", $date));
        break;
    default:
}

$offset        = $nb_ticks * $nb_unite;
$date_max      = CMbDT::dateTime("+ $offset $unite", $date_min);
$current       = CMbDT::dirac("hour", CMbDT::dateTime());
$temp_datetime = CMbDT::dateTime(null, $date_min);

// Pour l'affichage des prestations en mode journée
if ($granularite === "day") {
    $date_max = CMbDT::dateTime("-1 second", $date_max);
}

$datetimes = [];
$days      = [];
for ($i = 0; $i < $nb_ticks; $i++) {
    $offset = $i * $nb_unite;

    $datetime     = CMbDT::dateTime("+ $offset $unite", $date_min);
    $change_month = [];
    $datetimes[]  = $datetime;
    if ($granularite === "4weeks") {
        if (
            CMbDT::date($current) === CMbDT::date($temp_datetime)
            && CMbDT::time($current) >= CMbDT::time($temp_datetime)
            && CMbDT::time($current) > CMbDT::time($datetime)
        ) {
            $current = $temp_datetime;
        }
        $week_a = CMbDT::transform($temp_datetime, null, "%V");
        $week_b = CMbDT::transform($datetime, null, "%V");

        // les semaines
        $days[$datetime] = $week_b;

        // On stocke le changement de mois s'il advient
        if (CMbDT::transform($datetime, null, "%m") != CMbDT::transform($temp_datetime, null, "%m")) {
            // Entre deux semaines
            if ($i % 7 === 0) {
                $change_month[$week_a] = ["right" => $temp_datetime];
                $change_month[$week_b] = ["left" => $datetime];
            } else {
                // Dans la même semaine
                $change_month[$week_b] = ["left" => $temp_datetime, "right" => $datetime];
            }
        }
    } else {
        if (
            $granularite === "week"
            && CMbDT::date($current) === CMbDT::date($temp_datetime)
            && CMbDT::time($datetime) >= CMbDT::time($temp_datetime)
            && CMbDT::time($current) <= CMbDT::time($datetime)
        ) {
            $current = $temp_datetime;
        }
        // le datetime, pour avoir soit le jour soit l'heure
        $days[] = CMbDT::date($datetime);
    }
    $temp_datetime = $datetime;
}

$days = array_unique($days);

// Cas de la semaine 00
if ($granularite === "4weeks" && count($days) === 5) {
    array_pop($days);
}

$where["sejour.entree"] = "< '$date_max'";
$where["sejour.sortie"] = "> '$date_min'";

if ($duree_uscpo) {
    $ljoin["operations"]  = "operations.sejour_id = sejour.sejour_id";
    $where["duree_uscpo"] = "> 0";
}

if ($isolement) {
    $where["isolement"] = "= '1'";
}

$items_prestation = [];
if ($prestation_id) {
    $prestation = new CPrestationJournaliere();
    $prestation->load($prestation_id);
    $items_prestation = $prestation->loadBackRefs("items", "rank asc");
}

if ($item_prestation_id && $prestation_id) {
// L'axe de prestation a pu changer, donc ne pas appliquer l'item de prestation s'il ne fait pas partie de l'axe choisi
    if (isset($items_prestation[$item_prestation_id])) {
        $ljoin["item_liaison"]                 = "sejour.sejour_id = item_liaison.sejour_id";
        $where["item_liaison.item_souhait_id"] = " = '$item_prestation_id'";
    }
}

$sejours = $sejour->loadList($where, $order, null, null, $ljoin);

$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($sejours, "prestation_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
$services = CStoredObject::massLoadFwdRef($sejours, "service_id");

CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

foreach ($sejours as $_sejour_imc) {
    $_sejour_imc->loadRefPatient()->updateBMRBHReStatus($_sejour_imc);
    /* @var CAffectation $_affectation_imc */
    $conf_imc = $_sejour_imc->service_id ? "CService-" . $_sejour_imc->service_id : CGroups::loadCurrent();
    if (CAppUI::conf("dPhospi vue_temporelle show_imc_patient", $conf_imc)) {
        $_sejour_imc->_ref_patient->loadRefLatestConstantes(null, ["poids", "taille"]);
    }
}

$sejours_non_affectes = [];
$functions_filter     = [];
$operations           = [];
$suivi_affectation    = false;

// Chargement des affectations dans les couloirs (sans lit_id)
$where = [];

$ljoin = [
    "sejour"          => "sejour.sejour_id = affectation.sejour_id",
    "users_mediboard" => "sejour.praticien_id = users_mediboard.user_id",
    "users"           => "users_mediboard.user_id = users.user_id",
    "patients"        => "sejour.patient_id = patients.patient_id",
];

$where["lit_id"] = "IS NULL";
$where["sejour.group_id"] = "= '$group_id'";
if (is_array($services_ids) && count($services_ids)) {
    $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);
}
$where["affectation.entree"] = "<= '$date_max'";
$where["affectation.sortie"] = ">= '$date_min'";

$where["sejour.annule"] = "= '0'";

if ($duree_uscpo) {
    $ljoin["operations"]  = "operations.sejour_id = affectation.sejour_id";
    $where["duree_uscpo"] = "> 0";
}

if ($isolement) {
    $ljoin["sejour"]    = "sejour.sejour_id = affectation.sejour_id";
    $where["isolement"] = "= '1'";
}

if ($item_prestation_id && $prestation_id) {
    if (isset($items_prestation[$item_prestation_id])) {
        $ljoin["item_liaison"]                 = "affectation.sejour_id = item_liaison.sejour_id";
        $where["item_liaison.item_souhait_id"] = " = '$item_prestation_id'";
    }
}

$affectation = new CAffectation();

$affectations = $affectation->loadList($where, $order, null, null, $ljoin);
$_sejours     = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
$services     = $services + CStoredObject::massLoadFwdRef($affectations, "service_id");
$patients     = CStoredObject::massLoadFwdRef($_sejours, "patient_id");
CPatient::massCountPhotoIdentite($patients);
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

foreach ($_sejours as $_sejour) {
    /** @var $_sejour CSejour */
    $_sejour->loadRefPrestation();
    $_sejour->loadLiaisonsPonctualPrestationsForDay($date_min);
}

foreach ($affectations as $_affectation_imc) {
    $_affectation_imc->loadRefSejour()->loadRefPatient()->updateBMRBHReStatus($_affectation_imc->_ref_sejour);
    /* @var CAffectation $_affectation_imc */
    if (CAppUI::conf("dPhospi vue_temporelle show_imc_patient", "CService-" . $_affectation_imc->service_id)) {
        $_affectation_imc->_ref_sejour->_ref_patient->loadRefLatestConstantes(null, ["poids", "taille"]);
    }
}

// Préchargement des users
$user  = new CUser();
$where = ["user_id" => CSQLDataSource::prepareIn(CMbArray::pluck($_sejours, "praticien_id"))];
$users = $user->loadList($where);

$praticiens = CStoredObject::massLoadFwdRef($_sejours, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");
CStoredObject::massCountBackRefs($affectations, "affectations_enfant");
$_operations = CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");
CStoredObject::massLoadFwdRef($_operations, "plageop_id");

loadVueTempo(
    $sejours,
    $suivi_affectation,
    null,
    $operations,
    $date_min,
    $date_max,
    $period,
    $prestation_id,
    $functions_filter,
    $filter_function,
    $sejours_non_affectes
);
if (CAppUI::gconf("dPadmissions General show_deficience")) {
    CStoredObject::massLoadBackRefs($patients, "dossier_medical");
    $dossiers = CMbArray::pluck($sejours, "_ref_patient", "_ref_dossier_medical");
    CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
}

loadVueTempo(
    $affectations,
    $suivi_affectation,
    null,
    $operations,
    $date_min,
    $date_max,
    $period,
    $prestation_id,
    $functions_filter,
    $filter_function,
    $sejours_non_affectes
);

if (count($affectations) && CAppUI::gconf("dPadmissions General show_deficience")) {
    $dossiers = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "_ref_dossier_medical");
    CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
}
ksort($sejours_non_affectes, SORT_STRING);

$_sejour                  = new CSejour();
$_sejour->_type_admission = $_type_admission;

$smarty = new CSmartyDP();

$smarty->assign("sejours_non_affectes", $sejours_non_affectes);
$smarty->assign("_sejour", $_sejour);
$smarty->assign("triAdm", $triAdm);
$smarty->assign("functions_filter", $functions_filter);
$smarty->assign("filter_function", $filter_function);
$smarty->assign("granularite", $granularite);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("nb_ticks", $nb_ticks);
$smarty->assign("days", $days);
$smarty->assign("datetimes", $datetimes);
$smarty->assign("readonly", $readonly);
$smarty->assign("duree_uscpo", $duree_uscpo);
$smarty->assign("isolement", $isolement);
$smarty->assign("current", $current);
$smarty->assign("items_prestation", $items_prestation);
$smarty->assign("item_prestation_id", $item_prestation_id);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("td_width", CAffectation::$width_vue_tempo / $nb_ticks);
$smarty->assign("mode_vue_tempo", "classique");
$smarty->assign("affectations", $affectations);
$smarty->assign("sejours", $sejours);
$smarty->assign("services", $services);

$smarty->display("inc_vw_non_places.tpl");
