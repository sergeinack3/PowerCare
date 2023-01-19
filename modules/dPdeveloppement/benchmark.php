<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\Sessions\CSessionHandler;

CCanDo::checkRead();

// D�verrouiller la session pour rendre possible les requ�tes concurrentes.
CSessionHandler::writeClose();

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("module", "system");
$smarty->assign("action", "about");
$smarty->display("benchmark.tpl");