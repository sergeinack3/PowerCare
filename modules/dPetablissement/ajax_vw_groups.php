<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Etablissement\CLegalEntity;

CCanDo::checkRead();

$group_id = CView::get("group_id", "ref class|CGroups");

CView::checkin();

// Récupération du groupe selectionné
$group = new CGroups();
$group->load($group_id);

if ($group && $group->_id) {
    $group->loadFunctions();
    $group->loadRefsNotes();
    $group->loadRefLegalEntity();
    $group->loadRefLogo();

    if (CModule::getActive("medimail")) {
        $group->loadRefMedimailAccount();
    }
}

$legal_entity   = new CLegalEntity();
$legal_entities = $legal_entity->loadList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("group", $group);
$smarty->assign("legal_entities", $legal_entities);

$smarty->display("inc_vw_groups.tpl");
