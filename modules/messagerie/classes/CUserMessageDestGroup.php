<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Messagerie;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Represents a group of user message recipients
 */
class CUserMessageDestGroup extends CMbObject {
  /** @var integer Primary key */
  public $user_message_dest_group_id;

  /** @var string */
  public $name;

  /** @var string */
  public $color;

  /** @var int */
  public $group_id;

  /** @var CUserMessageDestGroupUserLink[] */
  public $_user_links;

  /**
   * @inheritdoc
   */
  public function getSpec(): CMbObjectSpec {
    $spec        = parent::getSpec();
    $spec->table = "user_message_dest_groups";
    $spec->key   = "user_message_dest_group_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps(): array {
    $props = parent::getProps();

    $props['name']     = 'str notNull';
    $props['color']    = 'color';
    $props['group_id'] = 'ref class|CGroups notNull back|user_message_dest_groups';

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;
  }

  /**
   * @return string|null
   * @throws Exception
   */
  public function delete() {
    $this->loadUsersLinks();
    foreach ($this->_user_links as $user_link) {
      $user_link->delete();
    }

    return parent::delete();
  }

  /**
   * @return CUserMessageDestGroupUserLink[]
   * @throws Exception
   */
  public function loadUsersLinks(): array {
    if ($this->_user_links = $this->loadBackRefs('dest_groups')) {
      $users = self::massLoadFwdRef($this->_user_links, 'user_id');
      self::massLoadFwdRef($users, 'function_id');

      foreach ($this->_user_links as $link) {
        $link->loadUser()->loadRefFunction();
      }
    }
    else {
      $this->_user_links = [];
    }

    return $this->_user_links;
  }
}
