<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

/**
 * dPbloc
 */
CCanDo::checkAdmin();

$hours = range(0, 23);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("hours", $hours);
$smarty->display("configure.tpl");
