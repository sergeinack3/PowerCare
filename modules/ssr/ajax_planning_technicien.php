<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

global $m;

CCando::checkRead();
$kine_id      = CView::get("kine_id", "ref class|CMediusers", true);
$surveillance = CView::get("surveillance", "bool default|0", true);
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$selectable   = CView::get("selectable", "bool default|0");
$height       = CView::get("height", "str");
$print        = CView::get("print", "bool default|0");
$large        = CView::get("large", "bool default|0");
$date         = CView::get("date", "date default|now", true);
$current_day  = CView::get("current_day", "bool default|0");
$day_used     = CView::get("day_used", "date default|now", true);
CView::checkin();

$kine = new CMediusers();
$kine->load($kine_id);

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$group                     = CGroups::loadCurrent();
$config_acte_presta        = CAppUI::gconf("ssr general use_acte_presta");
$see_name_element_planning = CAppUI::conf("ssr general see_name_element_planning", $group) || $m == "psy";

$nb_days_planning = $sejour->_id ?
    $sejour->getNbJourPlanning($date) :
    CEvenementSSR::getNbJoursPlanning($kine_id, $date);
if ($current_day) {
    $date             = $day_used;
    $nb_days_planning = 1;
}
$planning        = new CPlanningWeek(
    $date,
    null,
    null,
    $nb_days_planning,
    $selectable,
    $height,
    $large,
    !$print,
    $current_day ? $day_used : false
);
$planning->title = $surveillance ?
    "Séance non dédiée '$kine->_view'" :
    CAppUI::tr("Title-planning_ssr") . " $kine->_view'";

$planning->guid = $kine->_guid;
$planning->guid .= $surveillance ? "-surv" : "-tech";

// Chargement des evenement SSR
$evenement                     = new CEvenementSSR();
$where                         = [];
$where["debut"]                = "BETWEEN '$planning->_date_min_planning 00:00:00' AND '$planning->_date_max_planning 23:59:59'";
$where[]                       = "therapeute_id = '$kine->_id' OR therapeute2_id = '$kine->_id' OR therapeute3_id = '$kine->_id'";
$where["type_seance"]          = $surveillance ? " = 'non_dediee'" : " <> 'non_dediee'";
$where["seance_collective_id"] = " IS NULL";

/** @var CEvenementSSR[] $evenements */
$evenements = $evenement->loadList($where);

// Chargement des evenements SSR de "charge"
$where["type_seance"] = $surveillance ? " <> 'non_dediee'" : " = 'non_dediee'";

/** @var CEvenementSSR[] $evenements_charge */
$evenements_charge = $evenement->loadList($where);
foreach ($evenements_charge as $_evenement) {
    $planning->addLoad($_evenement->debut, $_evenement->duree);
}

CStoredObject::massLoadFwdRef($evenements, "prescription_line_element_id");
CStoredObject::massLoadFwdRef($evenements, "equipement_id");

$therapeutes = CStoredObject::massLoadFwdRef($evenements, "therapeute_id");
CStoredObject::massLoadFwdRef($therapeutes, "function_id");

CStoredObject::massCountBackRefs($evenements, "evenements_ssr");
CStoredObject::massCountBackRefs($evenements, "actes_cdarr");
CStoredObject::massCountBackRefs($evenements, "actes_csarr");
$sejours  = CStoredObject::massLoadFwdRef($evenements, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

foreach ($evenements as $_evenement) {
    $important = $sejour_id ? ($_evenement->sejour_id == $sejour_id) : true;

    $sejour     = $_evenement->loadRefSejour();
    $patient    = $sejour->loadRefPatient();
    $equipement = $_evenement->loadRefEquipement();

    $patient->updateBMRBHReStatus($sejour);

    // Title
    if ($_evenement->sejour_id) {
        $title = $patient->nom . " ";

        if ($patient->_bmr_bhre_status) {
            $smarty = new CSmartyDP("modules/dPpatients");
            $smarty->assign("patient", $patient);
            $bmr_bhre = $smarty->fetch("inc_icon_bmr_bhre");

            $title .= $bmr_bhre . " ";
        }
    } else {
        $title = $_evenement->_count["evenements_ssr"] . " patient(s)";
    }
    if ($large) {
        $title .= " " . substr($patient->prenom, 0, 2) . ".";
    }
    if (!$sejour_id && $_evenement->remarque) {
        $title .= " - " . $_evenement->remarque;
    }

    // Color
    $therapeute = $_evenement->loadRefTherapeute();
    $function   = $therapeute->loadRefFunction();
    $color      = "#$function->color";

    // Classes
    $class = "";
    if (!$_evenement->sejour_id && $_evenement->type_seance == "collective") {
        $_evenement->loadRefsEvenementsSeance();
        foreach ($_evenement->_ref_evenements_seance as $_seance) {
            if (!$_seance->countBackRefs("actes_cdarr")
                && !$_seance->countBackRefs("actes_csarr")
                && $config_acte_presta == 'csarr'
                && !$_seance->patient_missing) {
                $class = "zero-actes";
            }
            if ($_seance->countBackRefs("prestas_ssr") && $config_acte_presta == 'presta') {
                $class = "";
            }
            continue;
        }
    } else {
        if (!$_evenement->countBackRefs("actes_cdarr")
            && !$_evenement->countBackRefs("actes_csarr")
            && $config_acte_presta == 'csarr'
            && !$_evenement->patient_missing) {
            $class = "zero-actes";
        }
        if ($_evenement->countBackRefs("prestas_ssr") && $config_acte_presta == 'presta') {
            $class = "";
        }
    }

    $_sejour = $_evenement->_ref_sejour;
    if (!$_evenement->sejour_id && $_evenement->type_seance == "collective") {
        foreach ($_evenement->_ref_evenements_seance as $_seance) {
            $_sejour = $_seance->loadRefSejour();
            if (!CMbRange::in(
                $_seance->debut,
                CMbDT::date($_sejour->entree),
                CMbDT::date("+1 DAY", $_sejour->sortie)
            )) {
                $class = "disabled";
                continue;
            }
        }
    } elseif (!CMbRange::in(
        $_evenement->debut,
        CMbDT::date($_sejour->entree),
        CMbDT::date("+1 DAY", $_sejour->sortie)
    )) {
        $class = "disabled";
    }

    if ($_evenement->realise) {
        $class = "realise";
    }

    if ($_evenement->annule) {
        $class = "annule";
    }

    if ($_evenement->plage_groupe_patient_id) {
        $class .= " plage_groupe_patient";
    }

    $css_classes   = [];
    $css_classes[] = $class;
    $css_classes[] = $sejour->_guid;
    $css_classes[] = $equipement->_guid;

    $_evenement->clearBackRefCache("administrations_evt");
    $_evenement->loadRefsTransmissions();
    if (count($_evenement->_ref_transmissions)) {
        $css_classes[] = "transmission";
    }

    // Title and color in prescription case
    if ($line = $_evenement->loadRefPrescriptionLineElement()) {
        $element  = $line->_ref_element_prescription;
        $category = $element->loadRefCategory();
        $title    .= $m != "psy" ? " - " . $category->_view : "";
        $title    .= $see_name_element_planning ? " - " . $element->_view : "";

        // Color
        $color = $element->_color ? "#$element->_color" : null;

        // CSS Class
        $css_classes[] = $element->_guid;
        $css_classes[] = $category->_guid;
    }

    if ($_evenement->plage_groupe_patient_id) {
        $plage_groupe = $_evenement->loadRefPlageGroupePatient();
        $title .= "<br /> ($plage_groupe->_view)";
    }

    $event = new CPlanningEvent(
        $_evenement->_guid,
        $_evenement->debut,
        $_evenement->duree,
        $title,
        $color,
        $important,
        $css_classes
    );
    $planning->addEvent($event);
}

$config = $surveillance ? CAppUI::conf("ssr occupation_surveillance") : CAppUI::conf("ssr occupation_technicien");

// Labels de charge sur la journée
$ds    = CSQLDataSource::get("std");
$query = "SELECT SUM(duree) as total, DATE(debut) as date
  FROM evenement_ssr
  WHERE (therapeute_id = '$kine->_id' OR therapeute2_id = '$kine->_id' OR therapeute3_id = '$kine->_id') 
  AND debut BETWEEN '$planning->_date_min_planning 00:00:00' AND '$planning->_date_max_planning 23:59:59'";

$query .= $surveillance ? "AND type_seance = 'non_dediee'" : "AND type_seance <> 'non_dediee'";
$query .= " GROUP BY DATE(debut)";

$duree_occupation = $ds->loadList($query);

$occupations      = $duree_occupation;
$total_occupation = [];

foreach ($occupations as $_occupation) {
    if (isset($total_occupation[$_occupation["date"]])) {
        $total_occupation[$_occupation["date"]] += $_occupation["total"];
    } else {
        $total_occupation[$_occupation["date"]] = $_occupation["total"];
    }
}

foreach ($total_occupation as $_date => $_duree) {
    $color = "#fff";

    if ($_duree < $config["faible"]) {
        $color = "#8f8";
    }
    if ($_duree > $config["eleve"]) {
        $color = "#f88";
    }
    if ($_duree >= $config["faible"] && $_duree <= $config["eleve"]) {
        $color = "#ff4";
    }

    $planning->addDayLabel($_date, $_duree . " min", null, $color);
}

// Congés du personnel
/** @var CPlageConge $_plage */
foreach ($kine->loadBackRefs("plages_conge") as $_plage) {
    $planning->addUnavailability($_plage->date_debut, $_plage->date_fin);
}

// Activité du compte
if ($kine->deb_activite) {
    $deb = CMbDT::date("-1 DAY", $kine->deb_activite);
    $planning->addUnavailability(CMbDT::date("-1 WEEK", $deb), $deb);
}

if ($kine->fin_activite) {
    $fin = CMbDT::date("+1 DAY", $kine->fin_activite);
    $planning->addUnavailability($fin, CMbDT::date("+1 WEEK", $fin));
}

// Heure courante
$planning->showNow();
$planning->rearrange(true);

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("planning", $planning);
$smarty->assign("date", CMbDT::dateTime());
$smarty->display("inc_vw_week");
