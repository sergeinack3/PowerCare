<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$group       = new CGroups;
$groups_list = $group->loadList(null, "text");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("groups_list", $groups_list);
$smarty->display('configure.tpl');

