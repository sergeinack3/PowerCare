<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Read system message manager
 */
class CMessageAcquittement extends CStoredObject {
  /** @var integer Primary key */
  public $acquittement_msg_system_id;
  public $user_id;
  public $message_id;
  public $date;

  /** @var CMediusers */
  public $_ref_user;
  /** @var CMessage */
  public $_ref_message;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "acquittement_msg_system";
    $spec->key      = "acquittement_msg_system_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["user_id"]    = "ref class|CMediusers notNull back|user_acquittement";
    $props["message_id"] = "ref class|CMessage notNull back|acquittals";
    $props["date"]       = "dateTime notNull";

    return $props;
  }

  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  function loadRefMessage() {
    return $this->_ref_message = $this->loadFwdRef("message_id", true);
  }
}
