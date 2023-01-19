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
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\PlanningConsultService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningRange;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();

global $period, $periods, $listPraticiens, $chir_id, $function_id, $date_range, $ndate, $pdate, $plageconsult_id, $consultation_id;

$plageconsult_id = 0;

$_line_element_id = CValue::get("_line_element_id");

if (!$chir_id) {
    $chir_id = reset($listPraticiens);
}

$prat = new CMediusers;
$prat->load($chir_id);

//Planning au format  CPlanningWeek
$today         = CMbDT::date();
$debut         = $date_range;
$debut         = CMbDT::date("-1 week", $debut);
$debut         = CMbDT::date("next monday", $debut);
$fin           = CMbDT::date("next sunday", $debut);
$bank_holidays = array_merge(CMbDT::getHolidays($debut), CMbDT::getHolidays($fin));

// Nombre de jours
$nbDays        = 5;
$plage         = new CPlageconsult();
$whereInterv   = [];
$whereHP       = [];
$where         = [];
$where[]       = "chir_id = '$chir_id' OR remplacant_id = '$chir_id'";
$where["date"] = "= '$fin'";

if ($_line_element_id) {
    $where["pour_tiers"] = "= '1'";
}

if ($plage->countList($where)) {
    $nbDays = 7;
} else {
    $where["date"] = "= '" . CMbDT::date("-1 day", $fin) . "'";
    if ($plage->countList($where)) {
        $nbDays = 6;
    }
}

//Instanciation du planning
$planning           = new CPlanningWeek($debut, $debut, $fin, $nbDays, false, "auto");
$planning->title    = $prat->_view;
$planning->guid     = $prat->_guid;
$planning->hour_min = "07";
$planning->hour_max = "20";
$planning->pauses   = ["07", "12", "19"];

$planning_consult_service = new PlanningConsultService(
    $debut, $fin, $chir_id, null, null, null, null, $_line_element_id
);
$contents_by_date         = $planning_consult_service->getContentsByDate();

for ($i = 0; $i < $nbDays; $i++) {
    $jour = CMbDT::date("+$i day", $debut);
    $planning->addDayLabel($jour, '<span style="font-size: 1.4em">' . CMbDT::format($jour, "%a %d %b") . '</span>');

    if (CAppUI::pref("showIntervPlanning")) {
        // HORS PLAGE
        $horsPlages = $contents_by_date[$jour]["interv_hors_plage"];
        CMbObject::massLoadFwdRef($horsPlages, "chir_id");
        foreach ($horsPlages as $_horsplage) {
            $lenght = (CMbDT::minutesRelative("00:00:00", $_horsplage->temp_operation));
            $op     = new CPlanningRange(
                $_horsplage->_guid,
                $jour . " " . $_horsplage->time_operation,
                $lenght,
                $_horsplage,
                "3c75ea",
                "horsplage"
            );
            $planning->addRange($op);
        }

        // INTERVENTIONS
        /** @var CPlageOp[] $intervs */
        $intervs = $contents_by_date[$jour]["plage_op"];
        CMbObject::massLoadFwdRef($intervs, "chir_id");
        foreach ($intervs as $_interv) {
            $range = new CPlanningRange(
                $_interv->_guid,
                $jour . " " . $_interv->debut,
                CMbDT::minutesRelative($_interv->debut, $_interv->fin),
                CAppUI::tr($_interv->_class),
                "bbccee",
                "plageop"
            );
            $planning->addRange($range);
        }
    }

    $plages = $contents_by_date[$jour]["plage_consult"];
    CMbObject::massLoadFwdRef($plages, "chir_id");
    /** @var $_plage CPlageconsult */
    foreach ($plages as $_plage) {
        $_plage->loadRefsFwd(1);
        $_plage->loadRefsConsultations(false);

        $range        = new CPlanningRange(
            $_plage->_guid,
            $jour . " " . $_plage->debut,
            CMbDT::minutesRelative($_plage->debut, $_plage->fin)
        );
        $range->color = $_plage->color;
        $range->type  = "plageconsult";
        $planning->addRange($range);

        foreach ($_plage->_ref_slots as $_slot) {
            if ($_slot->status != "free") {
                continue;
            }
            $heure_debut        = CMbDT::format($_slot->start, "%H:%M:%S");
            $event              = new CPlanningEvent(
                "$jour $heure_debut",
                "$jour $heure_debut",
                $_plage->_freq,
                "",
                $_plage->_color_planning,
                true,
                null,
                null,
            );
            $event->type        = "rdvfree";
            $event->plage["id"] = $_plage->_id;
            if ($_plage->locked == 1) {
                $event->disabled = true;
            }
            $event->plage["color"] = $_plage->color;
            //Ajout de l'évènement au planning
            $planning->addEvent($event);
        }

        $consultations = $contents_by_date[$jour]["consults"];
        foreach ($consultations as $_consult) {
            if ($_consult->plageconsult_id != $_plage->_id) {
                continue;
            }
            $debute = "$jour $_consult->heure";
            $_consult->colorPlanning();
            if ($_consult->patient_id) {
                $_consult->loadRefPatient();
                $event = new CPlanningEvent(
                    $_consult->_guid,
                    $debute,
                    $_consult->duree * $_plage->_freq,
                    $_consult->_ref_patient->_view,
                    $_consult->_color_planning,
                    true,
                    null,
                    null
                );
            } else {
                $motif = "[" . CAppUI::tr("CConsultation-PAUSE") . "] $_consult->motif";
                $event = new CPlanningEvent(
                    $_consult->_guid,
                    $debute,
                    $_consult->duree * $_plage->_freq,
                    $motif,
                    $_consult->_color_planning,
                    true,
                    null,
                    null
                );
            }
            $event->type        = "rdvfull";
            $event->plage["id"] = $_plage->_id;

            if ($_plage->locked == 1) {
                $event->disabled = true;
            }

            $_consult->loadRefCategorie();
            if ($_consult->categorie_id) {
                $event->icon      = "./modules/dPcabinet/images/categories/" . basename(
                        $_consult->_ref_categorie->nom_icone
                    );
                $event->icon_desc = $_consult->_ref_categorie->nom_categorie;
            }
            //Ajout de l'évènement au planning
            $event->plage["color"] = $_plage->color;
            $planning->addEvent($event);
        }

        $_plage->colorPlanning($chir_id);
    }
}

$week = CMbDT::weekNumber($debut);
$planning->rearrange(true);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("period", $period);
$smarty->assign("periods", $periods);
$smarty->assign("date", $date_range);
$smarty->assign("refDate", $debut);
$smarty->assign("ndate", $ndate);
$smarty->assign("pdate", $pdate);
$smarty->assign("listPraticiens", $listPraticiens);
$smarty->assign("chir_id", $chir_id);
$smarty->assign("function_id", $function_id);
$smarty->assign("plageconsult_id", $plageconsult_id);
$smarty->assign("plage", $plage);
$smarty->assign("planning", $planning);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign("week", $week);
$smarty->assign("_line_element_id", $_line_element_id);

$smarty->display("inc_plage_selector_weekly.tpl");
