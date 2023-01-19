<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Developpement\CLogParser;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign('allowed_types', CLogParser::$log_types);
$smarty->display('vw_log_parser');