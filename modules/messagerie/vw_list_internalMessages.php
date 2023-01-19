<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

$user_id = CView::get('user_id', 'ref class|CMediusers');

CView::checkin();

$user = new CMediusers();
$user->load($user_id);

if (!$user->_id) {
  $user = CMediusers::get();
}

$selected_folder = CValue::get('selected_folder', 'inbox');

// Liste des messages reçus
$listInboxUnread = CUserMessageDest::countUnreadFor($user);

// Liste des messages archivés
$listArchived = CUserMessageDest::countArchivedFor($user);

// Liste des messages envoyés
$listSent = CUserMessageDest::countSentFor($user);

// Liste des brouillons
$countListDraft = CUserMessageDest::countDraftedFor($user);

$folders = array(
  'inbox'   => $listInboxUnread,
  'archive' => $listArchived,
  'sentbox' => $listSent,
  'draft'   => $countListDraft
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("user"            , $user);
$smarty->assign('folders'         , $folders);
$smarty->assign('selected_folder' , $selected_folder);

$smarty->display("vw_list_usermessages.tpl");
