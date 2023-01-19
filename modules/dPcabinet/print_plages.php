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
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\PlanningConsultImpressionService;
use Ox\Mediboard\Cabinet\PlanningConsultService;

CCanDo::checkRead();

$filter                  = new CConsultation;
$filter->plageconsult_id = CView::getRefCheckRead("plage_id", "ref class|CPlageconsult");
$filter->_date_min       = CView::get("_date_min", "date default|now");
$filter->_date_max       = CView::get("_date_max", "date default|now");
$filter->_telephone      = CView::get("_telephone", "bool default|1");
$filter->_coordonnees    = CView::get("_coordonnees", "str");
$filter->_plages_vides   = CView::get("_plages_vides", "bool default|1");
$filter->_non_pourvues   = CView::get("_non_pourvues", "bool default|1");
$canceled                = CView::get("canceled", "enum list|all|not_canceled|canceled default|not_canceled");
$filter->_print_ipp      = CView::get(
    "_print_ipp",
    "bool default|" . CAppUI::gconf("dPcabinet CConsultation show_IPP_print_consult")
);
$visite_domicile         = CView::get("visite_domicile", "bool default|0");
$libelle_plage           = CView::get("libelle", "str");
$plagesconsult_ids       = CView::get("plagesconsult_ids", "str");

$chir         = CView::getRefCheckRead("chir", "ref class|CMediusers");
$function_id  = CView::getRefCheckRead("function_id", "ref class|CFunctions");
$categorie_id = CView::get("category_id", "ref class|CConsultationCategorie");
CView::checkin();

$show_lit = false;

// On selectionne les plages
$plage = new CPlageconsult();

$plages_consult = [];

if ($filter->plageconsult_id) {
    $plage->load($filter->plageconsult_id);
    $filter->_date_min         = $filter->_date_max = $plage->date;
    $filter->_ref_plageconsult = $plage;
} elseif ($plagesconsult_ids) {
    $first_plage    = new CPlageconsult();
    $plages_consult = explode("|", $plagesconsult_ids);
    $first_plage->load($plages_consult[0]);
    $filter->_date_min = $filter->_date_max = $first_plage->date;
}

$planning_consult_impression_service = new PlanningConsultImpressionService(
    $filter->_date_min,
    $filter->_date_max,
    $function_id,
    $filter->plageconsult_id,
    $plages_consult,
    $libelle_plage,
    $chir
);
$contents = $planning_consult_impression_service->getContents();

// Pour chaque plage on selectionne les consultations
$listPlage = $contents["plage_consult"];

foreach ($listPlage as $plage) {
    $plage->listPlace = [];

    $i = 0;
    foreach ($plage->_ref_slots as $_slot) {
        if ($_slot->overbooked) {
            continue;
        }
        $plage->listPlace[$i]["time"]          = CMbDT::format($_slot->start, "%H:%M:%S");
        $plage->listPlace[$i]["consultations"] = [];
        $i++;
    }

    $consultations = $contents["consults"][$plage->_id];

    /** @var CConsultation $consultation */
    foreach ($consultations as $consultation) {
        // if the appointment respects the home visit filter
        // if the appointment respects the category filter
        // if the appointment respects the canceled filter
        if (
            ($visite_domicile && !$consultation->visite_domicile)
            || ($categorie_id &&
                (($consultation->categorie_id
                        && $consultation->categorie_id != $categorie_id)
                    || !$consultation->categorie_id))
            || ($canceled === "not_canceled" && $consultation->annule)
            || ($canceled === "canceled" && !$consultation->annule)
        ) {
            continue;
        }
        $patient = $consultation->_ref_patient;

        if ($consultation->sejour_id) {
            $patient->_ref_curr_affectation = $consultation->_ref_sejour->_ref_curr_affectation;
            if ($patient->_ref_curr_affectation->_id) {
                $show_lit = true;
            }
        }

        $keyPlace = CMbDT::timeCountIntervals($plage->debut, $consultation->heure, $plage->freq);
        for ($i = 0; $i < $consultation->duree; $i++) {
            if (!isset($plage->listPlace[($keyPlace + $i)]["time"])) {
                $plage->listPlace[($keyPlace + $i)]["time"] = CMbDT::time(
                    "+ " . $plage->_freq * $i . " minutes",
                    $consultation->heure
                );
                @$plage->listPlace[($keyPlace + $i)]["consultations"][] = $consultation;
            } else {
                @$plage->listPlace[($keyPlace + $i)]["consultations"][] = $consultation;
            }
        }
    }
}

// Suppression des plages vides
if (!$filter->_plages_vides) {
    foreach ($listPlage as $plage) {
        if (!count($plage->_ref_consultations)) {
            unset($listPlage[$plage->_id]);
        }
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("listPlage", $listPlage);
$smarty->assign("show_lit", $show_lit);
$smarty->assign("visite_domicile", $visite_domicile);

$smarty->display("print_plages.tpl");
