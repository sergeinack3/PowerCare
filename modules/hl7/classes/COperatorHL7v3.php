<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\Events\CHL7v3Event;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class COperatorHL7v3
 * Operator HL7v3
 */
class COperatorHL7v3 extends CEAIOperator {
  /**
   * Event
   *
   * @param CExchangeDataFormat $data_format Data format
   *
   * @return null|string
   */
  function event(CExchangeDataFormat $data_format) {
    $msg               = $data_format->_message;
    /** @var CHL7v3Event $evt */
    $evt                 = $data_format->_event_message;
      $evt->_data_format = $data_format;
    $evt->_sender        = $data_format->_ref_sender;

    // Récupération des informations du message
    /** @var CHL7v3MessageXML $dom_evt */
    $dom_evt = $evt->handle($msg);

    try {
      // Création de l'échange
      $exchange_hl7v3 = new CExchangeHL7v3();
      $exchange_hl7v3->load($data_format->_exchange_id);
      
      // Récupération des données du segment MSH
      $data = $dom_evt->getMSHEvenementXML();

      // Gestion de l'acquittement
      $ack = $dom_evt->getEventACK($evt);
      $ack->message_control_id = $data['identifiantMessage'];

      // Message non supporté pour cet utilisateur
      /*$evt_class = CHL7Event::getEventClass($evt);
      if (!in_array($evt_class, $data_format->_messages_supported_class)) {

      }*/

      $sender = $data_format->_ref_sender;
      $sender->getConfigs($data_format);

      $exchange_hl7v3->populateExchange($data_format, $evt);
      $exchange_hl7v3->message_valide = 1;
      
      // Gestion des notifications ? 
      if (!$exchange_hl7v3->_id) {
        $exchange_hl7v3->date_production      = CMbDT::dateTime();
        $exchange_hl7v3->identifiant_emetteur = $data['identifiantMessage'];
      }

      $exchange_hl7v3->store();
      
      // Pas de traitement du message
      if (!$data_format->_to_treatment) {
        return null;
      }

      $exchange_hl7v3->loadRefsInteropActor();

      // Chargement des configs de l'expéditeur
      $sender = $exchange_hl7v3->_ref_sender;
      $sender->getConfigs($data_format);

      $dom_evt->_ref_exchange_hl7v3 = $exchange_hl7v3;
      $ack->_ref_exchange_hl7v3     = $exchange_hl7v3;

      // Message XDM
      $msgAck = self::handleEvent($exchange_hl7v3, $dom_evt, $ack, $data);

      CHL7v2Message::resetBuildMode();
    }
    catch(Exception $e) {
      // À gérer
    }

    return $msgAck;
  }

  /**
   * Handle event XDM message
   *
   * @param CExchangeHL7v3     $exchange_hl7v3 Exchange HL7v3
   * @param CHL7v3MessageXML   $dom_evt        DOM Event
   * @param CHL7Acknowledgment $ack            Acknowledgment
   * @param array              $data           Nodes data
   *
   * @return null|string
   */
  static function handleEvent(CExchangeHL7v3 $exchange_hl7v3, CHL7v3MessageXML $dom_evt, CHL7Acknowledgment $ack, $data = array()) {
    $newPatient = new CPatient();
    $newPatient->_eai_exchange_initiator_id = $exchange_hl7v3->_id;

    $data = array_merge($data, $dom_evt->getContentNodes());

    return $dom_evt->handle($ack, $newPatient, $data);
  }
}
