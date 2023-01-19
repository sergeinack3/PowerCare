<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

// PAS DE PASSAGE AU CVIEW CAR SOUCI DE SESSION !!!

$services_ids   = CValue::getOrSession("services_ids", null);
$readonly       = CValue::get("readonly", 0);
$granularite    = CValue::getOrSession("granularite", "day");
$date           = CValue::getOrSession("date", CMbDT::dateTime());
$date_min       = CValue::getOrSession("date_min");
$date_max       = CValue::getOrSession("date_max");
$sens           = CValue::get("sens");
$mode_vue_tempo = CValue::getOrSession("mode_vue_tempo", "classique");
$readonly       = CValue::getOrSession("readonly", 0);
$prestation_id  = CValue::getOrSession("prestation_id", CAppUI::pref("prestation_id_hospi"));

// Si la date en session vient de la vue tableau, on retransforme en datetime
if (strpos($date, " ") === false) {
    $date = $date . " " . CMbDT::time();
}

$group = CGroups::loadCurrent();

if (CAppUI::conf("dPhospi prestations systeme_prestations", $group) == "standard") {
    CValue::setSession("prestation_id", "");
    $prestation_id = "";
}

if (is_array($services_ids)) {
    CMbArray::removeValue("", $services_ids);
    CMbArray::removeValue("NP", $services_ids);
}

if (!$services_ids) {
    $smarty = new CSmartyDP();
    $smarty->display("inc_no_services.tpl");
    CApp::rip();
}

$unite            = "";
$period           = "";
$datetimes        = [];
$change_month     = [];
$granularites = ["day", "48hours", "72hours", "week", "4weeks"];
$date_before_hour = $date_after_hour = "";

switch ($granularite) {
    case "day":
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

        $unite    = "hour";
        $nb_unite = 1;
        $nb_ticks = $hour_fin - $hour_debut + 1;
        $step     = "+1 hour";
        $period   = "1hour";

        // Réinitilisation de date_min si changement de date
        if ($date_min && $sens) {
            $date_min = CMbDT::dateTime($sens, $date_min);
        } else {
            $date_min = CMbDT::date($date) . " " . str_pad($hour_debut, 2, "0", STR_PAD_LEFT) . ":00:00";
        }

        // Mise en session du paramètre calculé
        CValue::setSession("date_min", $date_min);

        $date_before      = CMbDT::dateTime("-1 day", $date);
        $date_after       = CMbDT::dateTime("+1 day", $date);
        $date_before_view = CMbDT::dateTime("-1 day", $date_min);
        $date_before_hour = CMbDT::dateTime("-1 hour", $date_min);
        break;
    case "48hours":
        $unite            = "hour";
        $nb_unite         = 2;
        $nb_ticks         = 24;
        $step             = "+2 hours";
        $period           = "2hours";
        $date_min         = CMbDT::dateTime(CMbDT::date($date));
        $date_before      = CMbDT::dateTime("-1 day", CMbDT::date($date));
        $date_before_view = CMbDT::dateTime("-1 day", CMbDT::date($date_min));
        $date_after       = $date_after_view = CMbDT::dateTime("+2 days", $date);
        break;
    case "72hours":
        $unite            = "hour";
        $nb_unite         = 3;
        $nb_ticks         = 24;
        $step             = "+3 hours";
        $period           = "3hours";
        $date_min         = CMbDT::dateTime(CMbDT::date($date));
        $date_before      = CMbDT::dateTime("-1 day", CMbDT::date($date));
        $date_before_view = CMbDT::dateTime("-1 day", CMbDT::date($date_min));
        $date_after       = $date_after_view = CMbDT::dateTime("+3 days", $date_min);
        break;
    case "week":
        $unite            = "hour";
        $nb_unite         = 6;
        $nb_ticks         = 28;
        $step             = "+6 hours";
        $period           = "6hours";
        $date_min         = CMbDT::dateTime("-2 days", CMbDT::date($date));
        $date_before      = CMbDT::dateTime("-1 day", CMbDT::date($date));
        $date_before_view = CMbDT::dateTime("-1 day", CMbDT::date($date_min));
        break;
    case "4weeks":
        $unite            = "day";
        $nb_unite         = 1;
        $nb_ticks         = 28;
        $step             = "+1 day";
        $period           = "1day";
        $date_min         = CMbDT::dateTime("-1 week", CMbDT::dirac("week", $date));
        $date_before      = CMbDT::dateTime("-6 days", $date);
        $date_before_view = CMbDT::dateTime("-1 week", $date_min);
        break;
    default:
        break;
}

$current  = CMbDT::dirac("hour", CMbDT::dateTime());
$offset   = $nb_ticks * $nb_unite;
$date_max = CMbDT::dateTime("+ $offset $unite", $date_min);

switch ($granularite) {
    case "day":
        $date_after_view = $date_max;
        $date_after_hour = CMbDT::dateTime("+1 hour", $date_max);
        break;
    case "week":
        $date_after      = CMbDT::dateTime("+1 day", $date);
        $date_after_view = CMbDT::dateTime($date_max);
        break;
    case "4weeks":
        $date_after = $date_after_view = CMbDT::dateTime("+6 days", $date);
        break;
    default:
        break;
}

// Pour l'affichage des prestations en mode journée
if ($granularite == "day") {
    $date_max = CMbDT::dateTime("-1 second", $date_max);
}

$temp_datetime = CMbDT::dateTime(null, $date_min);

$days = [];

for ($i = 0; $i < $nb_ticks; $i++) {
    $offset = $i * $nb_unite;

    $datetime    = CMbDT::dateTime("+ $offset $unite", $date_min);
    $datetimes[] = $datetime;

    if ($granularite == "4weeks") {
        if (
            CMbDT::date($current) == CMbDT::date($temp_datetime) &&
            CMbDT::time($current) >= CMbDT::time($temp_datetime) && CMbDT::time($current) > CMbDT::time($datetime)
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
            if ($i % 7 == 0) {
                $change_month[$week_a] = ["right" => $temp_datetime];
                $change_month[$week_b] = ["left" => $datetime];
            } else {
                // Dans la même semaine
                $change_month[$week_b] = ["left" => $temp_datetime, "right" => $datetime];
            }
        }
    } else {
        if (
            $granularite == "week" && CMbDT::date($current) == CMbDT::date($temp_datetime) &&
            CMbDT::time($datetime) >= CMbDT::time($temp_datetime) && CMbDT::time($current) <= CMbDT::time($datetime)
        ) {
            $current = $temp_datetime;
        }
        if ($granularite) {
            // le datetime, pour avoir soit le jour soit l'heure
            $days[] = CMbDT::date($datetime);
        }
    }
    $temp_datetime = $datetime;
}

$days = array_unique($days);

// Cas de la semaine 00
if ($granularite == "4weeks" && count($days) == 5) {
    array_pop($days);
}

// Chargement des lits
$where                       = [];
$where["chambre.service_id"] = CSQLDataSource::prepareIn($services_ids);
$where["service.group_id"]   = " = '$group->_id'";
$where["chambre.annule"]     = "= '0'";
$where["lit.annule"]         = "= '0'";
$ljoin                       = [];
$ljoin["chambre"]            = "lit.chambre_id = chambre.chambre_id";
$ljoin["service"]            = "chambre.service_id = service.service_id";
$order                       = "ISNULL(chambre.rank), chambre.rank, chambre.nom, ISNULL(lit.rank), lit.rank";
$lit                         = new CLit();
/** @var CLit[] $lits */
$lits     = $lit->loadList($where, $order, null, null, $ljoin, null, null, false);
$chambres = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services = CStoredObject::massLoadFwdRef($chambres, "service_id");
$liaisons = CStoredObject::massLoadBackRefs($lits, "liaisons_items");
CStoredObject::massLoadFwdRef($liaisons, "item_prestation_id");

foreach ($lits as $_lit) {
    $_lit->_ref_affectations               = [];
    $chambre                               = $_lit->loadRefChambre();
    $chambre->_ref_lits[$_lit->_id]        = $_lit;
    $service                               = $chambre->loadRefService();
    $service->_ref_chambres[$chambre->_id] = $chambre;
    $liaisons_items                        = $_lit->_back["liaisons_items"];
    foreach ($liaisons_items as $_liaison) {
        $_liaison->loadRefItemPrestation();
    }
    $items_prestations = CMbArray::pluck($liaisons_items, "_ref_item_prestation");
    $prestations_ids   = CMbArray::pluck($items_prestations, "object_id");

    $_lit->_selected_item = new CItemPrestation();

    if (in_array($prestation_id, $prestations_ids)) {
        $inverse         = array_flip($prestations_ids);
        $item_prestation = $items_prestations[$inverse[$prestation_id]];
        if ($item_prestation->_id) {
            $_lit->_selected_item = $item_prestation;
        }
    }
}

CMbArray::pluckSort($services, SORT_ASC, "nom");

// Chargement des affectations
$where                       = [];
$where["lit_id"]             = CSQLDataSource::prepareIn(array_keys($lits));
$where["affectation.entree"] = "< '$date_max'";
$where["affectation.sortie"] = "> '$date_min'";
$where[]                     = "sejour.annule = '0' OR sejour.annule IS NULL";

$ljoin           = [];
$ljoin["sejour"] = "sejour.sejour_id = affectation.sejour_id";

$affectation     = new CAffectation();
$nb_affectations = $affectation->countList($where, null, $ljoin);
if ($nb_affectations > CAppUI::gconf("dPhospi General max_affectations_view")) {
    $smarty = new CSmartyDP();
    $smarty->display("inc_vw_max_affectations.tpl");
    CApp::rip();
}

$affectations = $affectation->loadList($where, "parent_affectation_id ASC", null, null, $ljoin);

foreach ($affectations as $_aff) {
    $_aff->loadRefsAffectations();
}

// Ajout des permissions
foreach ($services as $_service) {
    loadAffectationsPermissions($_service, CMbDT::date($date), 1, $prestation_id, $affectations);
}

// Ajout des prolongations anormales
// (séjours avec entrée réelle et sortie non confirmée et sortie < maintenant
$nb_days_prolongation = CAppUI::gconf("dPhospi vue_temporelle nb_days_prolongation");

if ($nb_days_prolongation) {
    $sejour = new CSejour();
    $max    = CMbDT::dateTime();
    $min    = CMbDT::date("-$nb_days_prolongation days", $max) . " 00:00:00";
    $where  = [
        "sejour.entree_reelle" => "IS NOT NULL",
        "sejour.sortie_reelle" => "IS NULL",
        "sejour.sortie_prevue" => "BETWEEN '$min' AND '$max'",
        "sejour.confirme"      => "IS NULL",
        "sejour.group_id"      => "= '$group->_id'",
        "sejour.annule"        => "= '0'",
    ];

    if (!CAppUI::conf("dPhospi vue_temporelle prolongation_ambu", $group)) {
        $where["sejour.type"] = "!= 'ambu'";
    }

    /** @var CSejour[] $sejours_prolonges */
    $sejours_prolonges = $sejour->loadList($where);

    $affectations_prolong = [];
    foreach ($sejours_prolonges as $_sejour) {
        $aff = $_sejour->getCurrAffectation($_sejour->sortie);
        if (!$aff->_id || !array_key_exists($aff->lit_id, $lits)) {
            continue;
        }
        $aff->_is_prolong        = true;
        $affectations[$aff->_id] = $aff;
    }
}

/** @var CSejour[] $sejours */
$sejours    = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
$operations = CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", ['annulee' => "= '0'"]);
CStoredObject::massLoadFwdRef($operations, "plageop_id");
CStoredObject::massCountBackRefs($affectations, "affectations_enfant");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CStoredObject::massLoadFwdRef($sejours, "prestation_id");
CPatient::massCountPhotoIdentite($patients);

foreach ($sejours as $_sejour) {
    $_sejour->loadRefPrestation();
    $_sejour->loadLiaisonsPonctualPrestationsForDay($date_min);
    $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
}

foreach ($affectations as $_affectation_imc) {
    /* @var CAffectation $_affectation_imc */
    if (CAppUI::conf("dPhospi vue_temporelle show_imc_patient", "CService-" . $_affectation_imc->service_id)) {
        $_affectation_imc->loadRefSejour()->loadRefPatient()->loadRefLatestConstantes(null, ["poids", "taille"]);
    }
}

if (CModule::getActive("dPImeds")) {
    CSejour::massLoadNDA($sejours);
}

$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");
$operations = [];

$suivi_affectation = false;
loadVueTempo($affectations, $suivi_affectation, $lits, $operations, $date_min, $date_max, $period, $prestation_id);

if (CAppUI::gconf("dPadmissions General show_deficience")) {
    CStoredObject::massLoadBackRefs($patients, "dossier_medical");
    $dossiers = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "_ref_dossier_medical");
    CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
}

foreach ($lits as $_lit) {
    $intervals = [];
    if (isset($_lit->_ref_affectations) && count($_lit->_ref_affectations)) {
        foreach ($_lit->_ref_affectations as $_affectation) {
            $intervals[$_affectation->_id] = [
                "lower" => $_affectation->entree,
                "upper" => $_affectation->sortie,
            ];
        }
        $_lit->_lines = CMbRange::rearrange($intervals);
    }
}

if (!CAppUI::gconf("dPhospi vue_temporelle hide_alertes_temporel")) {
    foreach ($lits as $_lit) {
        $_lit->_ref_chambre->checkChambre();
    }
}

$smarty = new CSmartyDP();

$smarty->assign("services", $services);
$smarty->assign("affectations", $affectations);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("date_before", $date_before);
$smarty->assign("date_before_view", $date_before_view);
$smarty->assign("date_before_hour", $date_before_hour);
$smarty->assign("date_after", $date_after);
$smarty->assign("date_after_view", $date_after_view);
$smarty->assign("date_after_hour", $date_after_hour);
$smarty->assign("granularites", $granularites);
$smarty->assign("granularite", $granularite);
$smarty->assign("nb_ticks", $nb_ticks);
$smarty->assign("datetimes", $datetimes);
$smarty->assign("days", $days);
$smarty->assign("change_month", $change_month);
$smarty->assign("mode_vue_tempo", $mode_vue_tempo);
$smarty->assign("readonly", $readonly);
$smarty->assign("nb_affectations", $nb_affectations);
$smarty->assign("readonly", $readonly);
$smarty->assign("current", $current);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("suivi_affectation", $suivi_affectation);
$smarty->assign("td_width", CAffectation::$width_vue_tempo / $nb_ticks);

$smarty->display("inc_vw_mouvements.tpl");
