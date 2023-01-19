<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$parturiente_id   = CValue::get("parturiente_id");
$large_icon       = CValue::get("large_icon");
$submit           = CValue::get("submit");
$modify_grossesse = CValue::get("modify_grossesse");
$show_empty       = CValue::get("show_empty");

$parturiente = new CPatient();
$parturiente->load($parturiente_id);

$parturiente->loadLastGrossesse();

$smarty = new CSmartyDP();

$smarty->assign("object", $parturiente);
$smarty->assign("patient", $parturiente);
$smarty->assign("large_icon", $large_icon);
$smarty->assign("submit", $submit);
$smarty->assign("modify_grossesse", $modify_grossesse);
$smarty->assign("show_empty", $show_empty);

$smarty->display("inc_input_grossesse");