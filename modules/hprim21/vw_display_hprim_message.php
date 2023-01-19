<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

/**
 * Affichage des messages Hprim21
 */
CCanDo::checkRead();

$message = CValue::getOrSession("message");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("message", $message);
$smarty->display("vw_display_hprim_message.tpl");
