<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;

/**
 * Edit discipline
 */
CCanDo::checkRead();
$discipline_id = CValue::getOrSession("discipline_id");

// R�cup�ration des groups
$groups = CGroups::loadGroups(PERM_EDIT);

// R�cup�ration de la fonction selectionn�e
$discipline = new CDiscipline();
$discipline->load($discipline_id);
$discipline->loadGroupRefsBack();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("discipline", $discipline);
$smarty->assign("groups"  , $groups);

$smarty->display("inc_edit_discipline.tpl");