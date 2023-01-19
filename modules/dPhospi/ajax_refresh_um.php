<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\PlanningOp\CUniteMedicale;

$um_id = CValue::get("um_id");
$uf_id = CValue::get("uf_id");
$uf    = new CUniteFonctionnelle();

if ($uf_id) {
  $uf->load($uf_id);
}

$um = new CUniteMedicale();
$um->load($um_id);

$smarty = new CSmartyDP();
$smarty->assign("um", $um);
$smarty->assign("uf", $uf);
$smarty->display("inc_vw_um_mode_hospit.tpl");