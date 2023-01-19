<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CUserMessageDestGroupUserLink extends CMbObject {
  /** @var integer Primary key */
  public $user_message_dest_group_user_id;

  /** @var int */
  public $group_id;

  /** @var int */
  public $user_id;

  /** @var CUserMessageDestGroup */
  public $_group;

  /** @var CMediusers */
  public $_user;

  /**
   * @inheritdoc
   */
  public function getSpec(): CMbObjectSpec {
    $spec        = parent::getSpec();
    $spec->table = "user_message_dest_group_users";
    $spec->key   = "user_message_dest_group_user_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps(): array {
    $props = parent::getProps();

    $props['group_id'] = 'ref class|CUserMessageDestGroup notNull back|dest_groups cascade';
    $props['user_id']  = 'ref class|CMediusers notNull back|dest_group_user cascade';

    return $props;
  }

  /**
   * Load the group
   *
   * @param bool $cache
   *
   * @return CUserMessageDestGroup
   * @throws Exception
   */
  public function loadDestGroup(bool $cache = true): CUserMessageDestGroup {
    return $this->_group = $this->loadFwdRef('group_id', $cache);
  }

  /**
   * Load the user
   *
   * @param bool $cache
   *
   * @return CMediusers
   * @throws Exception
   */
  public function loadUser(bool $cache = true): CMediusers {
    return $this->_user = $this->loadFwdRef('user_id', $cache);
  }
}
