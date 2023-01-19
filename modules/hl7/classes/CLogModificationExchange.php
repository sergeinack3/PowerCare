<?php
/**
 * @package Mediboard\hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CMbObject;
use Ox\Mediboard\Admin\CUser;

/**
 * Description
 */
class CLogModificationExchange extends CMbObject {
  // DB Table key
  public $log_modification_exchange_id;

  // DB references
  public $content_id;
  public $content_class;
  public $user_id;
  public $datetime_update;
  public $data_update;

  public $_ref_user;

  public $_data_update;

  /**
   * @see parent::getSpect()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "log_modification_exchange";
    $spec->key    = "log_modification_exchange_id";
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["content_id"]      = "ref class|CContentTabular notNull back|log_modification_exchange";
    $props["content_class"]   = "enum list|CContentTabular notNull";
    $props["user_id"]         = "ref notNull class|CUser back|log_modification_exchange";
    $props["datetime_update"] = "dateTime notNull";
    $props["data_update"]     = "text notNull";

    return $props;
  }

  /**
   * Load User
   *
   * @return null|CUser
   */
  function loadRefUser() {
    $this->_ref_user = $this->loadFwdRef("user_id");
  }
}
