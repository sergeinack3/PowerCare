<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkRead();

$canSante400 = CModule::getCanDo("dPsante400");
$dialog      = CValue::get("dialog");
$page        = intval(CValue::get('page', 0));

// Chargement du filtre
$filter               = new CIdSante400;
$filter->object_id    = CValue::get("object_id");
$filter->object_class = CValue::get("object_class");
$filter->tag          = CValue::get("tag");
$filter->id400        = CValue::get("id400");
$filter->nullifyEmptyFields();

if ($filter->object_id && $filter->object_class && !$filter->loadTargetObject()->getPerm(PERM_READ)) {
    CAppUI::accessDenied();
}

// Récupération de la liste des classes disponibles
if ($filter->object_class && $filter->object_id) {
  $listClasses = array($filter->object_class);
} else {
  $listClasses = CApp::getInstalledClasses(null, true);
}

// Vérification des permissions du module pour l'affichage du bouton de recherche de doublon
$is_admin = CCanDo::admin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page", $page);
$smarty->assign("filter", $filter);
$smarty->assign("canSante400", $canSante400);
$smarty->assign("listClasses", $listClasses);
$smarty->assign("dialog", $dialog);
$smarty->assign("is_admin", $is_admin);
$smarty->display("view_identifiants.tpl");
