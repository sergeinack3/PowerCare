<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

// Get session lifetime in php.ini
$gc_maxlifetime   = ini_get('session.gc_maxlifetime');
$session_lifetime = (($gc_maxlifetime)) ? (int)($gc_maxlifetime / 60) : 10;

$smarty = new CSmartyDP();
$smarty->assign('session_lifetime', $session_lifetime);
$smarty->display('configure.tpl');