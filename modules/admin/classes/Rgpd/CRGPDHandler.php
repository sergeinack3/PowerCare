<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;

use Ox\Core\CMbObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\CStoredObject;

/**
 * RGPD observer
 */
class CRGPDHandler extends ObjectHandler {
  static $first_store;

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return ((/*$object instanceof IRGPDCompliant || */$object instanceof IRGPDEvent) && parent::isHandled($object));
  }

  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!static::isHandled($object)) {
      return false;
    }

    static::$first_store = (!$object->_id);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    CMbObject::$useObjectCache = false;

    if (!static::isHandled($object)) {
      CMbObject::$useObjectCache = true;

      return false;
    }

    /** @var IRGPDEvent $object */
    if ($object->checkTrigger(static::$first_store)) {
      $object->triggerEvent();
    }

    CMbObject::$useObjectCache = true;

    return true;
  }
}
