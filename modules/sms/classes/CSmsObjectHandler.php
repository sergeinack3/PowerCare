<?php
/**
 * @package Mediboard\Sms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sms;

use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CEAIObjectHandler;

class CSmsObjectHandler extends CEAIObjectHandler {
  static $handled = array (
    "CProductDelivery",
    "CProductDeliveryTrace",
    "CAdministration",
  );

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
      return !$object->_ignore_eai_handlers && in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!parent::onAfterStore($object)) {
      return;
    }
    
    $this->sendFormatAction("onAfterStore", $object);
  }

  /**
   * @inheritdoc
   */
  function onBeforeMerge(CStoredObject $object) {
    if (!parent::onBeforeMerge($object)) {
      return;
    }
    
    $this->sendFormatAction("onBeforeMerge", $object);
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    if (!parent::onAfterMerge($object)) {
      return;
    }
    
    $this->sendFormatAction("onAfterMerge", $object);
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!parent::onAfterDelete($object)) {
      return;
    }
    
    $this->sendFormatAction("onAfterDelete", $object);
  }
}
