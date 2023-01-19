<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 *  * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Ucum\Ucum;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();

$smarty->assign("ucum_source", Ucum::getSource('Ucum'));
$smarty->assign("ucum_source_search", Ucum::getSource('UcumSearch'));

$smarty->display("configure.tpl");
