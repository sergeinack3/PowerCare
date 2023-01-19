<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Mediboard\Urgences\CCirconstance;

$circonstance       = new CCirconstance();
$list_circonstances = $circonstance->loadList(null, "Code");

$smarty = new CSmartyDP();
$smarty->assign("list_circonstances", $list_circonstances);
$smarty->display("vw_circonstances");