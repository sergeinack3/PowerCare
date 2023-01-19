<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Class CSaEventHprimXMLObjectHandler
 * SA Event H'XML Handler
 */

class CSaEventHprimXMLObjectHandler extends CHprimXMLObjectHandler {
  /**
   * @var array
   */
  static $handled = array ("COperation");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return;
    }

    $receiver = $object->_receiver;
    if (!$receiver->isMessageSupported("CHPrimXMLEvenementsServeurIntervention")) {
      return;
    }

    /** @var COperation $operation */
    $operation = $object;
    $sejour    = $operation->loadRefSejour();

    // Si le group_id du séjour est différent de celui du destinataire
    if ($sejour->group_id != $receiver->group_id) {
      return false;
    }
    $sejour->loadNDA($receiver->group_id);
    
    $patient = $sejour->loadRefPatient();
    $patient->loadIPP($receiver->group_id);

    // Si le patient n'est pas relié à AppFine, on n'envoie pas le message
    if (CModule::getActive("appFineClient")) {
      if ($receiver->_configs['send_appFine'] && !CAppFineClient::loadIdex($patient)->_id) {
        return;
      }
    }
    
    // Chargement des actes du codable
    $operation->loadRefsActes(); 
    
    $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurIntervention", $operation);   
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return;
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return;
    }
  }
}
