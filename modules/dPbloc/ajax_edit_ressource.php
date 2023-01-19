<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CRessourceMaterielle;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$ressource_id = CValue::getOrSession("ressource_id");
$type_ressource_id = CValue::get("type_ressource_id");

$ressource_materielle = new CRessourceMaterielle;
$ressource_materielle->load($ressource_id);

if (!$ressource_materielle->_id) {
  $ressource_materielle->group_id = CGroups::loadCurrent()->_id;
  $ressource_materielle->type_ressource_id = $type_ressource_id;
}

$ressource_materielle->loadRefTypeRessource();

$smarty = new CSmartyDP;

$smarty->assign("ressource_materielle", $ressource_materielle);

$smarty->display("inc_edit_ressource.tpl");
