<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Stock\CProductEndowment;

CCanDo::checkEdit();

$endowment_id = CValue::get('endowment_id');

$endowment = new CProductEndowment();
$endowment->load($endowment_id);

$group = new CGroups();

/** @var CGroups[] $groups */
$groups = $group->loadListWithPerms();

foreach ($groups as $_group) {
  $_group->loadRefsServices();
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('endowment', $endowment);
$smarty->assign('groups', $groups);

$smarty->display('inc_duplicate_endowment.tpl');
