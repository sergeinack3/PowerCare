<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;

/**
 * Onglet de configuration
 */
CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign("start", CMbDT::date());
$smarty->assign("end", CMbDT::date());

$smarty->display("configure.tpl");
