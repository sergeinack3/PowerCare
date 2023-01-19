<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$user_id = CView::get("user_id", "ref class|CMediusers");
$mod_id  = CView::get("mod_id", "ref class|CModule");

CView::checkin();

$user = new CMediusers();
$user->load($user_id);

$module = new CModule();
$module->load($mod_id);

$perm_profil = new CPermModule();
$perm_profil->user_id = $user->_profile_id;
$perm_profil->mod_id = $mod_id;
$perm_profil->loadMatchingObject();

$perm_user = new CPermModule();
$perm_user->user_id = $user->_id;
$perm_user->mod_id = $mod_id;
$perm_user->loadMatchingObject();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("user"       , $user);
$smarty->assign("module"     , $module);
$smarty->assign("perm_profil", $perm_profil);
$smarty->assign("perm_user"  , $perm_user);

$smarty->display("inc_manage_perm_module");
