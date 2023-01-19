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

$type_ressource = new CTypeRessource();
$type_ressource->group_id = CGroups::loadCurrent()->_id;

/** @var CTypeRessource[] $type_ressources */
$type_ressources = $type_ressource->loadMatchingList("libelle");

foreach ($type_ressources as $_type_ressource) {
  $_type_ressource->loadRefsRessources();
}

$smarty = new CSmartyDP;

$smarty->assign("type_ressource_id", $type_ressource_id);
$smarty->assign("type_ressources", $type_ressources);

$smarty->display("inc_list_type_ressources.tpl");
