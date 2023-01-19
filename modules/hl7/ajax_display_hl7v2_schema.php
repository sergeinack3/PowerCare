<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$path = CValue::get("path");

$smarty = new CSmartyDP();
$smarty->assign("path", $path);
$smarty->assign("content", file_get_contents($path));
$smarty->display("inc_display_hl7v2_schema.tpl");
