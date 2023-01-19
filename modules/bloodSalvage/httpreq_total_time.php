<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;

CCanDo::checkRead();
$blood_salvage_id = CValue::get("blood_salvage_id");
$totaltime        = CValue::getOrSession("totaltime", "00:00:00");

$blood_salvage = new CBloodSalvage();
$timeleft      = "06:00:00";

if ($blood_salvage_id) {
    $blood_salvage->load($blood_salvage_id);
    $blood_salvage->loadRefPlageOp();

    if ($blood_salvage->recuperation_start && $blood_salvage->transfusion_end) {
        $totaltime = CMbDT::timeRelative($blood_salvage->recuperation_start, $blood_salvage->transfusion_end);
    } elseif ($blood_salvage->recuperation_start) {
        $totaltime = CMbDT::timeRelative(
            $blood_salvage->recuperation_start,
            CMbDT::date($blood_salvage->_datetime) . " " . CMbDT::time()
        );
    }
    $timeleft = CMbDT::timeRelative($totaltime, "06:00:00");
    if ($totaltime > "06:00:00") {
        $timeleft = "00:00:00";
    }
}

$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("totaltime", $totaltime);
$smarty->assign("timeleft", $timeleft);

$smarty->display("inc_total_time.tpl");
