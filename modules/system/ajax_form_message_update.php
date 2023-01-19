<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\CMessage;

CCanDo::checkEdit();

$smarty = new CSmartyDP();
$smarty->assign("message", new CMessage());
$smarty->display("inc_form_message_update.tpl");
