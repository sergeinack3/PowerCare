<?php
/**
 * @package Mediboard\Patients
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
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();

$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$date_bh     = CView::get("date_bh", "date default|now");
$granularite = CView::get("granularite", "num", true);

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadPatientBanner();

$sejour->_date_entree = CMbDT::date($sejour->entree);
$sejour->_date_sortie = CMbDT::date($sejour->sortie);

if ($date_bh > $sejour->_date_sortie) {
    $date_bh = $sejour->_date_entree;
}

// Granularité
$affectations = $sejour->loadSurrAffectations();

$host = $affectations["curr"]->_id ? $affectations["curr"] : $affectations["prev"];
$host = $host->loadRefService();

if (!$host->_id) {
    $host = $sejour->loadRefEtablissement();
}

$granularite = $granularite ?: CConstantesMedicales::getHostConfig("bilan_hydrique_granularite", $host);

// Structure du bilan hydrique
$bilan = CPrescription::getStructureBilanHydrique(
    "$date_bh 00:00:00",
    "$date_bh 23:59:59",
    $granularite
);

// Depuis les constantes
$bh_cst = CConstantesMedicales::getValeursHydriques($sejour);

// Depuis la prescription
$bh_perfs     = [];
$prescription = $sejour->loadRefPrescriptionSejour();
if ($prescription && $prescription->_id) {
    $bh_perfs = $prescription->calculEntreesHydriques($granularite);
}

// Complétion du bilan
$keys_bilan    = array_keys($bilan);
$prev_datetime = reset($keys_bilan);
foreach ($bilan as $datetime => $_bh) {
    foreach ($bh_perfs as $datetime_perf => $_bh_perf) {
        if ($datetime_perf < $prev_datetime) {
            continue;
        }
        if ($datetime_perf < $datetime) {
            $bilan[$prev_datetime] += $_bh_perf;

            unset($bh_perfs[$datetime_perf]);
        }
    }

    $prev_datetime = $datetime;
}

$keys_bilan    = array_keys($bilan);
$prev_datetime = reset($keys_bilan);
foreach ($bilan as $datetime => $_bh) {
    foreach ($bh_cst as $datetime_cst => $_bh_cst) {
        if ($datetime_cst < $prev_datetime) {
            continue;
        }
        if ($datetime_cst < $datetime) {
            $bilan[$prev_datetime] += $_bh_cst["value"];

            unset($bh_cst[$datetime_cst]);
        }
    }

    $prev_datetime = $datetime;
}

// Graphique du bilan hydrique
$graph = [
    "title"   => CAppUI::tr("CConstantesMedicales-_bilan_hydrique"),
    "data"    => [],
    "options" => [
        "xaxis" => [
            "ticks"    => [],
            "position" => "bottom",
            "min"      => 0,
            "max"      => 0,
        ],
        "yaxis" => [
            "label"           => "",
            "position"        => "left",
            "autoscaleMargin" => 1,
            "min"             => min($bilan),
            "max"             => max($bilan),
        ],
        "grid"  => [
            "hoverable" => 1,
            "clickable" => 1,
        ],
    ],
];


$series = [
    [
        "data"  => [],
        "label" => CAppUI::tr("CConstantesMedicales-_bilan_hydrique") .
            " (" . CConstantesMedicales::$list_constantes["_bilan_hydrique"]["unit"] . ")",
        "color" => "#4da74d",
        "bars"  => [
            "show"      => 1,
            "fill"      => 1,
            "fillColor" => "#b7dcb7",
        ],
    ],
];

$ticks = [];

foreach ($bilan as $_datetime => $_bilan) {
    $series[0]["data"][] = [count($series[0]["data"]), round($_bilan, 2)];
    $ticks[]             = [count($ticks), CMbDT::dateToLocale(implode("<br />", explode(" ", $_datetime)))];
}


$graph["data"]                      = $series;
$graph["options"]["xaxis"]["ticks"] = $ticks;

$graph["options"]["xaxis"]["max"] = count($ticks);
$graph["options"]["yaxis"]["max"] = max($bilan);

$date_bh_before = CMbDT::date("-1 day", $date_bh);
$date_bh_after  = CMbDT::date("+1 day", $date_bh);

$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("graph", $graph);
$smarty->assign("date_bh", $date_bh);
$smarty->assign("date_bh_before", $date_bh_before);
$smarty->assign("date_bh_after", $date_bh_after);
$smarty->assign("granularite", $granularite);

$smarty->display("inc_bilan_hydrique");
