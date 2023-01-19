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
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

global $m;

CCanDo::checkRead();
$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$selectable  = CView::get("selectable", "bool default|0");
$height      = CView::get("height", "str");
$print       = CView::get("print", "bool default|0");
$large       = CView::get("large", "bool default|0");
$date        = CView::get("date", "date default|now", true);
$current_day = CView::get("current_day", "bool default|0");
$day_used    = CView::get("day_used", "date default|now", true);
CView::checkin();

if (!$sejour_id && isset($_SESSION[$m]["sejour_id"])) {
    $sejour_id = $_SESSION[$m]["sejour_id"];
}

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$patient = $sejour->loadRefPatient();

// Initialisation du planning
$nb_days_planning = $sejour->getNbJourPlanning($date);
if ($current_day) {
    $date             = $day_used;
    $nb_days_planning = 1;
}

$planning        = new CPlanningWeek(
    $date,
    $sejour->entree,
    $sejour->sortie,
    $nb_days_planning,
    $selectable,
    $height,
    $large,
    !$print,
    $current_day ? $day_used : false
);
$planning->title = "Patient '$patient->_view'";
$planning->guid  = "Planning-" . $sejour->_guid;

// Chargement des evenement SSR
$evenement                              = new CEvenementSSR();
$where                                  = [];
$where[]                                = "evenement_ssr.sejour_id = '$sejour->_id' AND evenement_ssr.seance_collective_id IS NULL";
$where["evenement_ssr.debut"]           = "BETWEEN '$planning->_date_min_planning 00:00:00' AND '$planning->_date_max_planning 23:59:59'";
$where["evenement_ssr.patient_missing"] = "= '0'";
//$where["evenement_ssr.plage_groupe_patient_id"] = " IS NULL";
$evenements = $evenement->loadList($where, null, null, "evenement_ssr_id");

// (ainsi que les seances collectives)
$evenement                                      = new CEvenementSSR();
$ljoin                                          = [];
$ljoin[]                                        = "evenement_ssr AS evt_seance ON (evt_seance.seance_collective_id = evenement_ssr.evenement_ssr_id)";
$where[0]                                       = "evenement_ssr.sejour_id IS NULL AND evt_seance.sejour_id = '$sejour->_id'";
$where["evenement_ssr.plage_groupe_patient_id"] = " IS NULL";
$evenements_collectifs                          = $evenement->loadList($where, null, null, "evenement_ssr_id", $ljoin);
$evenements                                     = array_merge($evenements, $evenements_collectifs);

$group                     = CGroups::loadCurrent();
$evenement_no_acte         = CAppUI::conf("ssr general use_acte_presta", $group) == 'aucun' ? 1 : 0;
$see_name_element_planning = CAppUI::conf("ssr general see_name_element_planning", $group);
$count_prestas_ssr         = 0;
/** @var CEvenementSSR[] $evenements */
foreach ($evenements as $_evenement) {
    $seance_collective = null;
    if (!$_evenement->sejour_id) {
        $seance_collective = $_evenement;
        // Chargement de l'evenement pour ce sejour
        $evt                       = new CEvenementSSR();
        $evt->sejour_id            = $sejour->_id;
        $evt->seance_collective_id = $_evenement->_id;
        $evt->loadMatchingObject();

        // On reaffecte les valeurs indispensables a l'affichage
        $evt->debut = $_evenement->debut;
        $evt->duree = $_evenement->duree;

        $draggable_guid = $_evenement->_guid;

        // Remplacement de la seance collective par le bon evenement
        $_evenement = $evt;
    } else {
        $draggable_guid = $_evenement->_guid;
    }

    // CSS Classes
    $class = $_evenement->type_seance == "non_dediee" ? "equipement" : "kine";
    if ($_evenement->seance_collective_id) {
        $class = "seance";
    }

    if (!$_evenement->countBackRefs("actes_cdarr") && !$_evenement->countBackRefs("actes_csarr")
        && !$_evenement->countBackRefs("prestas_ssr") && !$print && !$evenement_no_acte) {
        $class = "zero-actes";
    }

    if ($_evenement->realise && !$print) {
        $class = "realise";
    }

    if ($_evenement->annule && !$print) {
        $class = "annule";
    }
    if ($_evenement->seance_collective_id) {
        $class .= " seance_collective_id";
    }

    if ($_evenement->plage_groupe_patient_id) {
        $class .= " plage_groupe_patient";
    }

    $css_classes   = [];
    $css_classes[] = $class;

    // Title
    $therapeute = $_evenement->loadRefTherapeute();
    $title      = $therapeute->_view;

    // Color
    $function = $therapeute->loadRefFunction();
    $color    = "#$function->color";

    // Title and color in prescription case
    if ($line = $_evenement->loadRefPrescriptionLineElement()) {
        $element  = $line->_ref_element_prescription;
        $category = $element->loadRefCategory();

        $title = ($m != "psy") ? $category->_view : "";
        $title .= ($m != "psy" && $see_name_element_planning) ? " - " : "";
        $title .= ($m == "psy" || $see_name_element_planning) ? $element->_view : "";

        // Color
        $color = $element->_color ? "#$element->_color" : null;

        // CSS Class
        $css_classes[] = $element->_guid;
        $css_classes[] = $category->_guid;
    }

    // Title Equipement
    if ($print) {
        $equipement = $seance_collective ? $seance_collective->loadRefEquipement() : $_evenement->loadRefEquipement();
        $title      .= $equipement->_id ? " - " . $equipement->_view : '';
        $title      .= $_evenement->remarque ? "\n " . $_evenement->remarque : '';
    }

    if ($_evenement->plage_groupe_patient_id) {
        $plage_groupe = $_evenement->loadRefPlageGroupePatient();
        $title        .= "<br /> ($plage_groupe->_view)";
    }

    $debut = $_evenement->debut;

    // Instanciation
    $event               = new CPlanningEvent(
        $_evenement->_guid,
        $debut,
        $_evenement->duree,
        $title,
        $color,
        true,
        $css_classes,
        $draggable_guid
    );
    $event->draggable    = (CAppUI::pref("ssr_planning_dragndrop") == 1) && !$_evenement->realise && !$print;
    $event->resizable    = (CAppUI::pref("planning_resize") == 1) && !$_evenement->realise && !$print;
    $planning->dragndrop = CAppUI::pref("ssr_planning_dragndrop");
    $planning->addEvent($event);
}

$planning->showNow();
$planning->rearrange(true, true);

// Alertes séjour
$total_evenement = [];
foreach ($evenements as $_evenement) {
    if (!$_evenement->debut) {
        $plage_groupe = $_evenement->loadRefPlageGroupePatient();
        $debut        = CMbDT::date("$plage_groupe->groupe_day this week");
    } else {
        $debut = CMbDT::date($_evenement->debut);
    }

    if (!isset($total_evenement[$debut])) {
        $total_evenement[$debut]["duree"] = 0;
        $total_evenement[$debut]["nb"]    = 0;
    }
    $total_evenement[$debut]["duree"] += $_evenement->duree;
    $total_evenement[$debut]["nb"]++;
}

foreach ($total_evenement as $_date => $_total) {
    $alerts = [];
    if ($_total["duree"] < 120) {
        $alerts[] = "< 2h";
    }
    if ($_total["nb"] < 1) {
        $alerts[] = "0 indiv. ";
    }
    if ($count = count($alerts)) {
        $color = ($count == 2) ? "#f88" : "#ff4";
        $planning->addDayLabel($_date, implode(" / ", $alerts), null, $color);
    }
}

foreach ($sejour->loadRefReplacements() as $_replacement) {
    if ($_replacement->_id) {
        $_replacement->loadRefReplacer();
        $_replacement->loadRefConge();
        $conge =& $_replacement->_ref_conge;

        for ($day = $conge->date_debut; $day <= $conge->date_fin; $day = CMbDT::date("+1 DAY", $day)) {
            $planning->addDayLabel($day, $_replacement->_ref_replacer->_view);
        }
    }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("planning", $planning);
$smarty->display("inc_planning_sejour");
