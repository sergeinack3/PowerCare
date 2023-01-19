<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkRead();

$function_id = CView::get("function_id", "ref class|CFunctions");

CView::checkin();

// Récupération des groups
$groups = CGroups::loadGroups(PERM_EDIT);

// Récupération de la fonction selectionnée
$function = new CFunctions();
$function->load($function_id);

if ($function->_id) {
    $function->loadRefsNotes();
    $function->loadRefGroup();

    if (CModule::getActive("medimail")) {
        $function->loadRefMedimailAccount();
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("function", $function);
$smarty->assign("groups", $groups);

$smarty->display("inc_edit_function.tpl");
