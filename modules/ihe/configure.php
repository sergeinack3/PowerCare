<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkAdmin();

$function  = new CFunctions();
$functions = $function->loadList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("functions", $functions);
$smarty->display("configure.tpl");