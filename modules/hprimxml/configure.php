<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

/**
 * Configure
 */
CCanDo::checkAdmin();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->display("configure.tpl");
