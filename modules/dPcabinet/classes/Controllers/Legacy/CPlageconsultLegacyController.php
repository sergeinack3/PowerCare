<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CSlot;

class CPlageconsultLegacyController extends CLegacyController
{
    public function vw_list_next_slots(): void
    {
        $this->checkPermRead();

        $prats_ids     = CView::get("prats_ids", "str");
        $days          = CView::get("days", "str", true);
        $times         = CView::get("times", "str", true);
        $libelle_plage = CView::get("libelle_plage", "str", true);
        $week_number   = CView::get("week_number", "str", true);
        $year          = CView::get("year", "str");
        $rdv           = CView::get("rdv", "bool");

        CView::checkin();

        $prats_ids = explode(",", $prats_ids);
        $days      = explode(",", $days);
        $times     = explode(",", $times);

        $dates_week = CMbDT::dateFromWeekNumber($week_number, $year);
        $monday     = CMbDT::date("this week", $dates_week["start"]);

        $first_day = $days[array_key_first($days)];
        if ($first_day == "Monday") {
            $date_start = $monday;
        } else {
            $date_start = CMbDT::date("next $first_day", $monday);
        }
        if ($date_start < CMbDT::date()) {
            $date_start = CMbDT::date();
        }

        $days_of_week      = [
            "Monday"    => 2,
            "Tuesday"   => 3,
            "Wednesday" => 4,
            "Thursday"  => 5,
            "Friday"    => 6,
            "Saturday"  => 7,
        ];
        $days_not_selected = [];
        foreach ($days_of_week as $_day => $key) {
            if (!in_array($_day, $days)) {
                $days_not_selected[] = $key;
            }
        }

        $where = [];
        $slots = [];

        //DAYOFWEEK(date_inscription) = 2
        if (count($days_not_selected)) {
            $where[] = "DAYOFWEEK(slot.start) " . CSQLDataSource::prepareNotIn($days_not_selected);
        }

        $where["plageconsult.chir_id"] = CSQLDataSource::prepareIn($prats_ids);

        $where[] = "DATE(slot.start) >= '$date_start'";

        if ($libelle_plage) {
            $where['plageconsult.libelle'] = " LIKE '%$libelle_plage%'";
        }

        $ljoin["plageconsult"] = "plageconsult.plageconsult_id = slot.plageconsult_id";

        $where['plageconsult.locked'] = " != '1'";
        $where['slot.status']         = " = 'free'";
        $where_time                   = [];
        if ($times) {
            $time_start = "07:00:00";
            $serie      = false;
            foreach ($times as $_time) {
                if (!$serie) {
                    $time_start = $_time;
                }
                if (in_array(CMbDT::addTime("+ 01:00:00", $_time), $times)) {
                    $serie = true;
                } else {
                    $where_time[] = "TIME(slot.start) >= '$time_start' AND TIME(slot.end) BETWEEN '$time_start' AND '" . CMbDT::addTime(
                            "+ 01:00:00",
                            $_time
                        ) . "'";
                    $serie        = false;
                }
            }
        }
        $where[] = implode(' OR ', $where_time);

        $where[] = "IF (DATE(slot.start) = '" . CMbDT::date() . "',  
                        IF (TIME(slot.start) >= '" . CMbDT::time() . "', true, false), 
                        true
                    )";

        $slot  = new CSlot();
        $slots = $slot->loadList($where, "start ASC", 10, null, $ljoin);

        CStoredObject::massLoadFwdRef($slots, "plageconsult_id");
        foreach ($slots as $_slot) {
            $_slot->loadRefPlageconsult();
            $_slot->_ref_plageconsult->loadRefChir();
            $date            = CMbDT::format($_slot->start, "%y-%m-%d");
            $_slot->_date    = CMbDT::strftime('%A %d %B', strtotime($date));
            $_slot->_heure   = CMbDT::format($_slot->start, "%H:%M:%S");
            $_slot->_nb_week = CMbDT::format($date, "%W");
        }

        // Génération du content
        $this->renderSmarty('inc_show_slots', ["slots" => $slots, "rdv" => $rdv]);
    }
}
