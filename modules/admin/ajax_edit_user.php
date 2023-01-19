<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkEdit();

// Récuperation de l'utilisateur sélectionné
$user_id  = CView::get("user_id", "str", true);
$tab_name = CView::get("tab_name", "str default|identity");

CView::checkin();

CView::enforceSlave(false);

if ($user_id) {
    $user = CUser::findOrFail($user_id);

    if (CUser::get()->_id != $user->_id) {
        $user->needsEdit();
    }
} else {
    $user = new CUser();
}

// Chargement du détail de l'utilisateur
$user->loadRefMediuser();
$user->loadRefsNotes();
$user->isLDAPLinked();

// Chargement des utilateurs associés
if ($user->template) {
    $user->loadRefProfiledUsers();
}

CMbArray::naturalSort(CUser::$types);

$token = AntiCsrf::prepare()
    ->addParam('user_id', ($user->_id) ?: null)
    ->addParam('callback', 'UserPermission.callback')
    ->addParam('_duplicate')
    ->addParam('_duplicate_username')
    ->addParam('user_username')
    ->addParam('user_type')
    ->addParam('template')
    ->addParam('_user_password')
    ->addParam('_user_password2')
    ->addParam('user_last_name')
    ->addParam('user_first_name')
    ->addParam('user_email')
    ->addParam('is_robot')
    ->addParam('dont_log_connection')
    ->addParam('force_change_password')
    ->addParam('allow_change_password')
    ->getToken();

$password_spec_builder  = $user->getPasswordSpecBuilder();
$weak_prop              = $password_spec_builder->getWeakSpec()->getProp();
$strong_prop            = $password_spec_builder->getStrongSpec()->getProp();
$ldap_prop              = $password_spec_builder->getLDAPSpec()->getProp();
$admin_prop             = $password_spec_builder->getAdminSpec()->getProp();
$password_configuration = $password_spec_builder->getConfiguration();

$smarty = new CSmartyDP();
$smarty->assign('weak_prop', $weak_prop);
$smarty->assign('strong_prop', $strong_prop);
$smarty->assign('ldap_prop', $ldap_prop);
$smarty->assign('admin_prop', $admin_prop);
$smarty->assign('password_configuration', $password_configuration);
$smarty->assign('token', $token);
$smarty->assign("utypes", CUser::$types);
$smarty->assign("user", $user);
$smarty->assign("tab_name", $tab_name);
$smarty->assign("specs", $user->getProps());
$smarty->assign("is_admin", (CAppUI::$user->isAdmin() || CUser::get(CAppUI::$instance->user_id)->isSuperAdmin()));

$smarty->display("inc_edit_user");
