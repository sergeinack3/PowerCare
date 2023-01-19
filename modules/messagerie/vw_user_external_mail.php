<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Messagerie\CUserMailFolder;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$user_connected = CMediusers::get();
$account_id = CView::get("account_id", 'num');
$selected_folder = CView::get('selected_folder', 'str default|inbox');

$account = new CSourcePOP();
$account->load($account_id);
if ($account_id) {
  CView::setSession("account_id", $account_id);
}

CView::checkin();

/* Vérification de l'accès à distance à la messagerie */
if (!CAppUI::gconf('messagerie access external_access') && !CAppUI::isIntranet()) {
  $smarty = new CSmartyDP();
  $smarty->assign('type', 'error');
  $smarty->assign('msg', CAppUI::tr('messagerie-msg-external_access_disabled'));
  $smarty->display('inc_display_msg.tpl');
  CApp::rip();
}

//user is atempting to see an account private from another medisuers
if (($account->object_id != $user_connected->_id) && ($account->is_private)) {
  CAppUI::stepAjax("CSourcePOP-error-private_account", UI_MSG_ERROR);
}

$folders = array(
  'inbox'     => array(
    'count' => CUserMail::countUnread($account_id),
    'folders' => CUserMailFolder::loadFolders($account, 'inbox')
  ),
  'archived'  => array(
    'count' => CUserMail::countArchived($account_id),
    'folders' => CUserMailFolder::loadFolders($account, 'archived')
  ),
  'favorites' => array(
    'count' => CUserMail::countFavorites($account_id),
    'folders' => CUserMailFolder::loadFolders($account, 'favorites')
  ),
  'sentbox'   => array(
    'count' => CUserMail::countSent($account_id),
    'folders' => CUserMailFolder::loadFolders($account, 'sentbox')
  ),
  'drafts'    => array(
    'count' => CUserMail::countDrafted($account_id),
    'folders' => CUserMailFolder::loadFolders($account, 'drafts')
  )
);

$object = null;
if (!in_array($selected_folder, array_keys($folders))) {
  $object = CUserMailFolder::loadFromGuid($selected_folder);
  $object->loadAncestors();
}

$smarty = new CSmartyDP();
$smarty->assign("account", $account);
$smarty->assign('folders', $folders);
$smarty->assign('selected_folder', $selected_folder);
$smarty->assign('object', $object);
$smarty->display("vw_account_mail.tpl");