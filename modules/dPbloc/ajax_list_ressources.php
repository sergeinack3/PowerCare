<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CRessourceMaterielle;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$ressource_id = CValue::getOrSession("ressource_id");

$ressource_materielle = new CRessourceMaterielle();

$where = array();
$where["ressource_materielle.group_id"] = "= '".CGroups::loadCurrent()->_id."'";

$ljoin = array();
$ljoin["type_ressource"] = "ressource_materielle.type_ressource_id = type_ressource.type_ressource_id";

/** @var CRessourceMaterielle[] $ressources_materielles */
$ressources_materielles = $ressource_materielle->loadList($where, "type_ressource.libelle", null, null, $ljoin);

CMbObject::massLoadFwdRef($ressources_materielles, "type_ressource_id");

foreach ($ressources_materielles as $_ressource) {
  $_ressource->loadRefTypeRessource();
}

$smarty = new CSmartyDP;

$smarty->assign("ressource_id", $ressource_id);
$smarty->assign("ressources_materielles", $ressources_materielles);

$smarty->display("inc_list_ressources.tpl");
