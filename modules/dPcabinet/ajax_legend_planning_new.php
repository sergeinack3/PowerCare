<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkRead();

//smarty
$smarty = new CSmartyDP();
$smarty->assign("view_operations", CAppUI::pref("showIntervPlanning"));
$smarty->display("vw_legend_planning_new.tpl");