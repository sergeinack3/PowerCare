<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id       = CValue::get("sejour_id");
$sejour_id_futur = CValue::get("sejour_id_futur");

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour_merge = new CSejour();
$sejour_merge->load($sejour_id_futur);


$sejour_merge->entree_reelle  = $sejour->entree_reelle;
$sejour_merge->mode_entree_id = $sejour->mode_entree_id;
$sejour_merge->mode_entree    = $sejour->mode_entree;
$sejour_merge->provenance     = $sejour->provenance;

try {
    $sejour_merge->checkMerge([$sejour]);
    $msg = null;
} catch (Throwable $t) {
    $msg = $t->getMessage();
}

$smarty = new CSmartyDP();
$smarty->assign("message", $msg);
$smarty->display("inc_result_check_merge");
