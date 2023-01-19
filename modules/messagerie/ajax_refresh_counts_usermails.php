<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMail;

CCanDo::checkRead();

$account_id = CView::get('account_id', 'num');

CView::checkin();

$counts = array(
  array('name' => 'inbox', 'count' =>  CUserMail::countUnread($account_id)),
  array('name' => 'archived', 'count' =>  CUserMail::countArchived($account_id)),
  array('name' => 'sentbox', 'count' =>  CUserMail::countSent($account_id)),
  array('name' => 'favorites', 'count' =>  CUserMail::countFavorites($account_id)),
  array('name' => 'drafts', 'count' => CUserMail::countDrafted($account_id))
);

echo json_encode($counts);