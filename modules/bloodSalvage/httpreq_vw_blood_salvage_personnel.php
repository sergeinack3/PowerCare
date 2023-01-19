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
use Ox\Mediboard\BloodSalvage\Services\BloodSalvageService;
use Ox\Mediboard\Personnel\CPersonnel;

$blood_salvage_id = CValue::getOrSession("blood_salvage_id");
$blood_salvage    = new CBloodSalvage();

$date = CValue::getOrSession("date", CMbDT::date());

$modif_operation = CCanDo::edit() || $date >= CMbDT::date();

$list_nurse_sspi = CPersonnel::loadListPers("reveil");

$tabAffected  = [];
$timingAffect = [];

if ($blood_salvage_id) {
    $blood_salvage->load($blood_salvage_id);
    BloodSalvageService::loadAffected($blood_salvage_id, $list_nurse_sspi, $tabAffected, $timingAffect);
}

$smarty = new CSmartyDP();

$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("list_nurse_sspi", $list_nurse_sspi);
$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("tabAffected", $tabAffected);
$smarty->assign("timingAffect", $timingAffect);

$smarty->display("inc_vw_blood_salvage_personnel.tpl");
