<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use \Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;

/**
 * Class CUserMessageDestGroupController
 *
 * @package Ox\Mediboard\Messagerie
 */
class CUserMessageDestGroupController extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct('CUserMessageDestGroup', 'user_message_dest_group_id');

    $this->redirect = 'm=messagerie';
  }

  /**
   * @inheritDoc
   */
  public function doStore() {
    parent::doStore();
    /** @var CUserMessageDestGroup $group */
    $group = $this->_obj;

    $added_user_id = explode('|', $this->request['added_users_id']);
    $added_user_number = 0;
    foreach ($added_user_id as $user_id) {
      $link = new CUserMessageDestGroupUserLink();
      $link->group_id = $group->_id;
      $link->user_id = $user_id;
      $link->loadMatchingObject();

      if (!$link->_id) {
        if ($link->store() === null) {
          $added_user_number++;
        }
      }
    }

    $removed_link_ids = explode('|', $this->request['removed_links_id']);
    $removed_links_number = 0;
    foreach ($removed_link_ids as $link_id) {
      $link = CUserMessageDestGroupUserLink::find($link_id);
      if ($link && $link->delete() === null) {
        $removed_links_number++;
      }
    }

    if ($added_user_number) {
      $msg = $added_user_number > 1 ?  'CUserMessageDestGroupUserLink-msg-create|pl' : 'CUserMessageDestGroupUserLink-msg-created';
      CAppUI::setMsg($msg, UI_MSG_OK, $added_user_number);
    }

    if ($removed_links_number) {
      $msg = $removed_links_number > 1 ?  'CUserMessageDestGroupUserLink-msg-delete|pl' : 'CUserMessageDestGroupUserLink-msg-created';
      CAppUI::setMsg($msg, UI_MSG_OK, $removed_links_number);
    }
  }

  /**
   * @inheritDoc
   */
  public function doDelete() {
    /** @var CUserMessageDestGroup $group */
    $group = $this->_obj;

    $group->loadUsersLinks();
    foreach ($group->_user_links as $user_link) {
      $user_link->delete();
    }

    parent::doDelete();
  }
}