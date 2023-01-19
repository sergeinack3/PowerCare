<?php 
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Messagerie\CUserMailFolder;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$account_id = CView::get("account_id", 'num');
$selected_folder = CView::get('selected_folder', 'str default|inbox');

CView::checkin();

$account = new CSourcePOP();
$account->load($account_id);

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
  if ($object && $object->_id) {
    $object->loadAncestors();
  }
  else {
    $selected_folder = 'inbox';
  }
}

$smarty = new CSmartyDP();
$smarty->assign('account', $account);
$smarty->assign('folders', $folders);
$smarty->assign('selected_folder', $selected_folder);
$smarty->assign('object', $object);
$smarty->display('inc_mail_folders.tpl');