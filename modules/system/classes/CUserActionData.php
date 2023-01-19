<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CStoredObject;

/**
 * User-Action-Data link class
 */
class CUserActionData extends CStoredObject {
  /** @var integer Primary key */
  public $user_action_data_id;

  /** @var integer Action id */
  public $user_action_id;

  /** @var string field of object */
  public $field;

  /** @var string value of object */
  public $value;

  // Object References
  public $_ref_user_action;


  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->loggable = false;
    $spec->table    = 'user_action_data';
    $spec->key      = 'user_action_data_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["user_action_id"] = "ref notNull class|CUserAction cascade back|user_action_datas";
    $props["field"]          = "str notNull";
    $props["value"]          = "text";

    //$props["value"] = "text compress";

    return $props;
  }

  /**
   * Load the user_action
   *
   * @param bool $cache Use object cache
   *
   * @return CUserAction
   */
  function loadRefUserAction($cache = true) {
    return $this->_ref_user_action = $this->loadFwdRef("user_action_id", $cache);
  }


  /**
   * @inheritdoc
   * @deprecated
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadRefUserAction();
  }


  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "{$this->field} - {$this->value}";
  }


}