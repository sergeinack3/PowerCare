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
use Ox\Mediboard\Bloc\CTypeRessource;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$type_ressource_id = CValue::getOrSession("type_ressource_id");

$type_ressource = new CTypeRessource;
$type_ressource->load($type_ressource_id);

if (!$type_ressource->_id) {
  $type_ressource->group_id = CGroups::loadCurrent()->_id;
}

$smarty = new CSmartyDP;

$smarty->assign("type_ressource", $type_ressource);

$smarty->display("inc_edit_type_ressource.tpl");
