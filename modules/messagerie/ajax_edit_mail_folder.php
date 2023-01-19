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
use Ox\Mediboard\Messagerie\CUserMailFolder;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkEdit();

$account_id = CView::get('account_id', 'ref class|CSourcePOP');
$folder_id = CView::get('folder_id', 'ref class|CUserMailFolder');

CView::checkin();

$account = CSourcePOP::loadFromGuid("CSourcePOP-$account_id");

$folder = new CUserMailFolder();
if ($folder_id) {
  $folder->load($folder_id);

  if ($folder->type == 'drafts') {
    $folders = array(
      'drafts' => CUserMailFolder::loadFolders($account, 'drafts')
    );
  }
  elseif ($folder->type == 'sentbox') {
    $folders = array(
      'sentbox' => CUserMailFolder::loadFolders($account, 'sentbox')
    );
  }
  else {
    $folders = array(
      'inbox'     => CUserMailFolder::loadFolders($account, 'inbox'),
      'archived'  => CUserMailFolder::loadFolders($account, 'archived'),
      'favorites' => CUserMailFolder::loadFolders($account, 'favorites')
    );
  }
}
else {
  $folder->account_id = $account->_id;

  $folders = array(
    'inbox'     => CUserMailFolder::loadFolders($account, 'inbox'),
    'archived'  => CUserMailFolder::loadFolders($account, 'archived'),
    'favorites' => CUserMailFolder::loadFolders($account, 'favorites'),
    'sentbox'   => CUserMailFolder::loadFolders($account, 'sentbox'),
    'drafts'    => CUserMailFolder::loadFolders($account, 'drafts')
  );
}

$smarty = new CSmartyDP();
$smarty->assign('folder', $folder);
$smarty->assign('folders', $folders);
$smarty->display('inc_edit_mail_folder.tpl');