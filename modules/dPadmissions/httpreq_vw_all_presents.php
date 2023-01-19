<?php

/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour   = new CSejour();
$pec_list = $sejour->_specs["type_pec"]->_list;

$date               = CView::get("date", ["date", "default" => CMbDT::date()], true);
$heure              = CView::get("heure", "time", true);
$services_ids       = CView::get("services_ids", "str", true);
$admission_type_ids = CView::get("sejours_ids", "str", true); // Admission type
$enabled_service    = CView::get("active_filter_services", "bool default|0", true);
$prat_id            = CView::get("prat_id", "num", true);
$type_pec           = CView::get("type_pec", ["str", "default" => $pec_list]);
$only_entree_reelle = CView::get("only_entree_reelle", "bool default|0", true);

CView::checkin();

// Date helpers
$month_min     = CMbDT::date("first day of +0 month", $date);
$lastmonth     = CMbDT::date("last day of previous month", $date);
$nextmonth     = CMbDT::date("first day of next month", $date);
$bank_holidays = CMbDT::getHolidays($date);
$yesterday     = CMbDT::date("- 1 day", $date);
$tomorrow      = CMbDT::date("+ 1 day", $date);

if (is_array($services_ids)) {
    CMbArray::removeValue("", $services_ids);
}

$type_pref = [];
if (is_array($admission_type_ids)) {
    CMbArray::removeValue("", $admission_type_ids);
    // Admission type list
    $list_type_admission = $sejour->_specs["_type_admission"]->_list;

    // Get filters of different stay types
    foreach ($admission_type_ids as $_admission_type_id) {
        if ($_admission_type_id != 0) {
            $type_pref[] = $list_type_admission[$_admission_type_id];
        }
    }
}

// Init the monthly table
$days = [];
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
    $days[$day] = 0;
}

// Used to prepare sql queries
$ds = CSQLDataSource::get("std");

$where = [
    "sejour.type" => (count($type_pref) > 0) ? CSQLDataSource::prepareIn($type_pref) : CSQLDataSource::prepareNotIn(
        array_merge(CSejour::getTypesSejoursUrgence(), ["seances"])
    ),
];
$ljoin = [];

// Services filter
if (count($services_ids) > 0 && $enabled_service) {
    $ljoin["affectation"]            = "affectation.sejour_id = sejour.sejour_id";
    $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);
}

// Practitioner filter
if ($prat_id) {
    $user = CMediusers::get($prat_id);

    if ($user->isAnesth()) {
        $ljoin['operations'] = "sejour.sejour_id = operations.sejour_id";
        $ljoin['plagesop']   = "plagesop.plageop_id = operations.plageop_id";

        $where[] = $ds->prepare(
            "operations.anesth_id = :prat_id 
                   OR plagesop.anesth_id = :prat_id 
                   OR sejour.praticien_id = :prat_id",
            $prat_id
        );
    } else {
        $where[] = $ds->prepare("sejour.praticien_id = ?", $prat_id);
    }
}

$group = CGroups::loadCurrent();

$where["sejour.annule"]   = "= '0'";
$where["sejour.group_id"] = $ds->prepare("= ?", $group->_id);

if ($only_entree_reelle) {
    $where["sejour.entree_reelle"] = "IS NOT NULL";
}

$where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);

// Admission list per day
foreach ($days as $_date => $num) {
    // Make min and max hours using filters
    $min_hour = ($heure) ?: "00:00:00";
    $max_hour = ($heure) ?: "23:59:59";

    // Prepare dates filter
    $date_min = CMbDT::dateTime($min_hour, $_date);
    $date_max = CMbDT::dateTime($max_hour, $_date);

    // Prepare dates conditions
    $prepare_entry = $ds->prepare("<= ?", $date_max);
    $prepare_exit  = $ds->prepare(">= ?", $date_min);

    // Choose the table which will be used to filter
    $table_where_key = (count($services_ids) > 0 && $enabled_service) ? "affectation" : "sejour";

    // Add wheres
    $where[$table_where_key . ".entree"] = $prepare_entry;
    $where[$table_where_key . ".sortie"] = $prepare_exit;

    if ($enabled_service && is_array($services_ids) && count($services_ids)) {
        /* When there is a filter by service, the where clauses for the date are on the affectations,
         * which can cause a single sejour id to be counted several times,
         * as there can be multiple affectation in a service for a single sejour */
        $days[$_date] = count($sejour->countMultipleList($where, null, 'affectation.sejour_id', $ljoin));
    } else {
        /* No where clauses on the affection in this case, a simple countList will do the trick */
        $days[$_date] = $sejour->countList($where, null, $ljoin);
    }
}

$smarty = new CSmartyDP();

$smarty->assign("hier", $yesterday);
$smarty->assign("demain", $tomorrow);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign('date', $date);
$smarty->assign('lastmonth', $lastmonth);
$smarty->assign('nextmonth', $nextmonth);
$smarty->assign('days', $days);
$smarty->assign('enabled_service', $enabled_service);

$smarty->display('inc_vw_all_presents.tpl');
