<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CAppUI;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Hospi\CAffectation;

/**
 * Modification des affectations des bébés pour suivre le lit de la parturiente
 */
class CAffectationHandler extends ObjectHandler {
  static $handled = array("CAffectation");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    if (!CModule::getActive("dPhospi")) {
      return false;
    }

    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return;
    }
    /** @var $object CAffectation */
    /** @var $_affectation CAffectation */
    foreach ($object->loadRefsAffectationsEnfant() as $_affectation) {
      $_affectation->lit_id = $object->lit_id;
      if ($msg = $_affectation->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }
    }
  }
}
