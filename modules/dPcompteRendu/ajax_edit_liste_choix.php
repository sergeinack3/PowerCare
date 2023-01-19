<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CListeChoix;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Modification de liste de choix
 */

CCanDo::checkRead();

$user_id  = CValue::getOrSession("user_id");
$liste_id = CValue::getOrSession("liste_id");

// Utilisateurs disponibles
$user = CMediusers::get($user_id);

// Accès aux listes de choix de la fonction et de l'établissement
$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CListeChoix access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CListeChoix access_group");

// Liste sélectionnée
$liste = new CListeChoix();
if (!CMediusers::get()->isAdmin()) {
  $liste->user_id = $user->_id;
}
$liste->load($liste_id);
if ($liste->_id) {
  if ($liste->function_id && !$access_function) {
    CAppUI::accessDenied();
  }
  if ($liste->group_id && !$access_group) {
    CAppUI::accessDenied();
  }
}

$liste->loadRefOwner();
$liste->loadRefModele();
$liste->loadRefsNotes();

$modeles = CCompteRendu::loadAllModelesFor($user->_id, "prat", null, "body");

$owners  = $user->getOwners();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("modeles"         , $modeles);
$smarty->assign("owners"          , $owners);

$smarty->assign("access_function" , $access_function);
$smarty->assign("access_group"    , $access_group);

$smarty->assign("user"            , $user);
$smarty->assign("liste"           , $liste);

$smarty->display("inc_edit_liste_choix");
