<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;

CCanDo::checkRead();
$blood_salvage_id = CValue::getOrSession("blood_salvage_id");
$date             = CValue::getOrSession("date", CMbDT::date());
$modif_operation  = CCanDo::edit() || $date >= CMbDT::date();
$timing           = CValue::getOrSession("timing");

$blood_salvage = new CBloodSalvage();
if ($blood_salvage_id) {
    $blood_salvage->load($blood_salvage_id);
    $timing["_recuperation_start"] = [];
    $max_add_minutes               = CAppUI::gconf("dPsalleOp Timing_list max_add_minutes");
    foreach ($timing as $key => $value) {
        for (
            $i = -CAppUI::gconf(
                "dPsalleOp Timing_list max_sub_minutes"
            ); $i < $max_add_minutes && $blood_salvage->$key !== null; $i++
        ) {
            $timing[$key][] = CMbDT::time("$i minutes", $blood_salvage->$key);
        }
    }
}
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("date", $date);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("timing", $timing);

$smarty->display("inc_vw_recuperation_start_timing.tpl");
