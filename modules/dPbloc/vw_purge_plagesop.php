<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$purge_start_date = CMbDT::date();
$purge_limit      = 100;
$practitioners    = CMediusers::get()->loadPraticiens();

$smarty = new CSmartyDP();
$smarty->assign("purge_start_date", $purge_start_date);
$smarty->assign("purge_limit",      $purge_limit);
$smarty->assign("practitioners",    $practitioners);
$smarty->display("vw_purge_plagesop.tpl");