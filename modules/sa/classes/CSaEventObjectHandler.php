<?php
/**
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sa;

use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CEAIObjectHandler;

/**
 * Class CSaEventObjectHandler
 * SA Event Handler
 */

class CSaEventObjectHandler extends CEAIObjectHandler {
  /**
   * @var array
   */
  static $handled = array ("COperation", "CConsultation", "CPrescriptionLineElement");

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
  function onBeforeDelete(CStoredObject $object) {
    if (!parent::onBeforeDelete($object)) {
      return;
    }
    
    $this->sendFormatAction("onBeforeDelete", $object);
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
