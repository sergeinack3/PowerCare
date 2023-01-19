<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CClassMap;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\CStoredObject;

/**
 * Class CInteropActorHandler
 * Interop actor handler
 */
class CInteropActorHandler extends ObjectHandler {
  /** @var array */
  static $handled = array("CInteropActor");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    foreach (self::$handled as $_handled_class) {
      if (is_subclass_of($object, CClassMap::getSN($_handled_class))) {
        return true;
      }
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    // Cas d'une fusion
    if ($object->_merging) {
      return false;
    }

    if ($object->_forwardRefMerging) {
      return false;
    }

    // Send e-mail if actor create
    CEAITools::notifyNewActor($object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if (!$object->_merging) {
      return false;
    }


    return true;
  }

  /**
   * @inheritdoc
   */
  function onMergeFailure(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if (!$object->_merging) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }
}