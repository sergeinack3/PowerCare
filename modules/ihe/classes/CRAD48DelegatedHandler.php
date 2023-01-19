<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Cabinet\CConsultation;

/**
 * Class CRAD48DelegatedHandler 
 * RAD48 Delegated Handler
 */
class CRAD48DelegatedHandler extends CITIDelegatedHandler {
  static $handled        = array ("CConsultation");
  protected $profil      = "SWF";
  protected $message     = "SIU";
  protected $transaction = "RAD48";

  /**
   * @inheritDoc
   */
  static function isHandled(CStoredObject $mbObject) {
    return in_array($mbObject->_class, self::$handled);
  }

  /**
   * @see parent::onAfterStore()
   */
  function onAfterStore(CStoredObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }

    /** @var CConsultation $consultation */
    $consultation = $mbObject;

    /** @var CReceiverHL7v2 $receiver */
    $receiver = $consultation->_receiver;

    if ($consultation->annule && !$consultation->fieldModified("annule")) {
      return false;
    }

    // Si on ne souhaite explicitement pas de synchro
    if ($consultation->_no_synchro_eai && $consultation->_link_appfine === false) {
        return false;
    }

    // Consultation venant d'AppFine, on va uniquement le renvoyer à Galaxie
    if (CModule::getActive('appFineClient')) {
      if ($consultation->_link_appfine === true && $receiver->type !== CInteropActor::ACTOR_GALAXIE) {
          return false;
      }
    }

    $praticien = $consultation->loadRefPraticien();
    if (!$praticien || !$praticien->_id) {
      return false;
    }

    $function = $praticien->loadRefFunction();
    if (!$function || !$function->_id) {
      return false;
    }

    // Si le group_id de la fonction du chir est différent de celui du destinataire
    if ($function->group_id != $receiver->group_id) {
      return false;
    }

    $patient = $consultation->loadRefPatient();
    $consultation->loadLastLog();
    
    // Récupération du code du trigger    
    $code = $this->getCode($consultation);

    /** @var CInteropReceiver $receiver */
    $receiver = $mbObject->_receiver;
    $receiver->getInternationalizationCode($this->transaction);

    // Gestion d'AppFine dans le handler
    if (CModule::getActive("appFineClient")) {
      if ($receiver->_configs['send_evenement_to_mbdmp']) {
        if (!CAppFineClient::checkRGPD($patient, $receiver->group_id)) {
          return;
        }
      }

      if ($receiver->_configs['send_evenement_to_mbdmp']
          && (!CAppFineClient::loadIdex($patient)->_id && !$consultation->fieldModified("patient_id"))
      ) {
        return false;
      }
    }

    if (!$this->isMessageSupported($this->message, $code, $receiver)) {
      return false;
    }
    
    $this->sendITI($this->profil, $this->transaction, $this->message, $code, $consultation);    
  }

  /**
   * @see parent::onBeforeDelete()
   */
  function onBeforeDelete(CStoredObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    return true;
  }

  /**
   * @see parent::onAfterDelete()
   */
  function onAfterDelete(CStoredObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }

    return true;
  }
  
  function getCode(CStoredObject $consultation) {
    $current_log = $consultation->loadLastLog();
    if (!in_array($current_log->type, array("create", "store"))) {
      return null;
    }

    $consultation->loadOldObject();

    // Création d'un rendez-vous
    if ($current_log->type == "create") {
      return "S12";
    } 
    
    // Déplacement d'un rendez-vous (heure ou plageconsult_id)
    if ($consultation->fieldModified("heure") || $consultation->fieldModified("plageconsult_id")) {
      return "S13";
    }
    
    // Annulation d'un rendez-vous
    if ($consultation->fieldModified("annule", "1")) {
      return "S15";
    }
    
    // Modification d'un rendez-vous
    return "S14";
  }
}
