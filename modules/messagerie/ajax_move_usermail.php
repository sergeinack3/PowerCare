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

CCanDo::checkEdit();

$mail_ids = json_decode(stripslashes(CView::get('usermail_ids', 'str default|[]')));
$account_id = CView::get('account_id', 'ref class|CSourcePOP');

CView::checkin();

/** @var CSourcePOP $account */
$account = CSourcePOP::loadFromGuid("CSourcePOP-$account_id");

$mail = new CUserMail();
if (count($mail_ids)) {
  $mail->load($mail_ids[0]);
  if ($mail->sent) {
    $folders = array('sentbox' => CUserMailFolder::loadFolders($account, 'sentbox'));
  }
  elseif ($mail->draft) {
    $folders = array('drafts' => CUserMailFolder::loadFolders($account, 'drafts'));
  }
  else {
    $folders = array(
      'inbox'     => CUserMailFolder::loadFolders($account, 'inbox'),
      'archived'  => CUserMailFolder::loadFolders($account, 'archived'),
      'favorites' => CUserMailFolder::loadFolders($account, 'favorites'),
    );
  }

  if (count($mail_ids) > 1) {
    $mail = new CUserMail();
  }
}
else {
  $folders = array(
    'inbox'     => CUserMailFolder::loadFolders($account, 'inbox'),
    'archived'  => CUserMailFolder::loadFolders($account, 'archived'),
    'favorites' => CUserMailFolder::loadFolders($account, 'favorites'),
    'sentbox'   => CUserMailFolder::loadFolders($account, 'sentbox'),
    'drafts'    => CUserMailFolder::loadFolders($account, 'drafts')
  );
}

$mail = new CUserMail();
if (count($mail_ids) == 1) {
  $mail->load($mail_ids[0]);
}

$smarty = new CSmartyDP();
$smarty->assign('mail', $mail);
$smarty->assign('folders', $folders);
$smarty->display('inc_move_mail.tpl');