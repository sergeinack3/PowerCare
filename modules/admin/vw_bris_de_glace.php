<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$date_start = CValue::getOrSession("date_start", CMbDT::date());
$date_end = CValue::getOrSession("date_end", $date_start);

// smarty
$smarty = new CSmartyDP();
$smarty->assign("bris", new CBrisDeGlace());
$smarty->assign("date_start", $date_start);
$smarty->assign("date_end", $date_end);
$smarty->assign("patient", new CPatient());
$smarty->display("vw_bris_de_glace.tpl");
