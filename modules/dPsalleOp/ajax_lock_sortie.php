<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$curr_user = CMediusers::get();
$anesths = $curr_user->loadAnesthesistes();

$smarty = new CSmartyDP();

$smarty->assign("anesths", $anesths);

$smarty->display("inc_lock_sortie.tpl");