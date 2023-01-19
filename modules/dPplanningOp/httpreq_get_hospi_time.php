<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stats\CTempsHospi;

CCanDo::checkRead();

$chir_id    = CValue::get("chir_id"    , 0 );
$codes      = CValue::get("codes"      , "");
$javascript = CValue::get("javascript" , true);

$codes = explode("|", $codes);
$result = CTempsHospi::getTime($chir_id, $codes);
$temps = $result ? sprintf("%.2f", $result)."j" : "-";

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("temps", $temps);
$smarty->assign("javascript", $javascript);

$smarty->display("inc_get_time");
