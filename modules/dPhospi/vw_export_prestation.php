<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign('types_admission', implode(', ', CSejour::$types));
$smarty->display('vw_export_prestation.tpl');