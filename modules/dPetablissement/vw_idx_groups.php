<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Etablissement\CLegalEntity;

CCanDo::checkRead();

CView::checkin();

// récupération des Entités Juridiques
$legal_entity = new CLegalEntity();
$legal_entities = $legal_entity->loadList();

// Récupération des fonctions
$groups = CGroups::loadGroups(PERM_READ);
CStoredObject::massLoadFwdRef($groups, "legal_entity_id");

foreach ($groups as $_group) {
  $_group->loadFunctions();
  $_group->loadRefLegalEntity();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("groups"        , $groups);
$smarty->assign("legal_entities", $legal_entities);

$smarty->display("vw_idx_groups.tpl");
