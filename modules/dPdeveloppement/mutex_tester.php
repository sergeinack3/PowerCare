<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkRead();

$actions = array(
  "stall",
  "die",
  "run",
  "lock",
  "dummy",
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("actions", $actions);

$smarty->display("mutex_tester.tpl");
