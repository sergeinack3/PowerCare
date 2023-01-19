<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

CView::checkin();

$user = CMediusers::get();

$counts = array(
  array('name' => 'inbox', 'count' =>  CUserMessageDest::countUnreadFor($user)),
  array('name' => 'archive', 'count' =>  CUserMessageDest::countArchivedFor($user)),
  array('name' => 'sentbox', 'count' =>  CUserMessageDest::countSentFor($user)),
  array('name' => 'draft', 'count' =>  CUserMessageDest::countDraftedFor($user))
);

echo json_encode($counts);