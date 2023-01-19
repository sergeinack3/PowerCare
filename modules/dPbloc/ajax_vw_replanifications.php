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
use Ox\Core\CValue;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$date_replanif = CValue::getOrSession("date_replanif", CMbDT::date());

$smarty = new CSmartyDP;

$smarty->assign("date_replanif", $date_replanif);

$smarty->display("inc_vw_replanifications.tpl");
