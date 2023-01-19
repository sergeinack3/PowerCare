<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;

CCanDo::checkRead();
$blood_salvage_id = CValue::postOrSession("blood_salvage_id");

$blood_salvage = new CBloodSalvage();
if ($blood_salvage_id) {
    $blood_salvage->load($blood_salvage_id);
    $blood_salvage->loadRefs();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);

$smarty->display("inc_vw_cell_saver_volumes.tpl");
