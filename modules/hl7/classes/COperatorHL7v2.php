<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\V2\Handle\LinkACK;
use Ox\Mediboard\Doctolib\CSenderHL7v2Doctolib;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class COperatorHL7v2
 * Operator HL7v2
 */
class COperatorHL7v2 extends CEAIOperator
{
    /**
     * Event
     *
     * @param CExchangeDataFormat $data_format Data format
     *
     * @return null|string
     */
    function event(CExchangeDataFormat $data_format)
    {
        $msg = $data_format->_message;
        /** @var CHL7v2Event $evt */
        $evt               = $data_format->_event_message;
        $evt->_data_format = $data_format;
        $evt->_sender      = $data_format->_ref_sender;

        // Récupération des informations du message
        /** @var CHL7v2MessageXML $dom_evt */
        $dom_evt           = $evt->handle($msg);
        $dom_evt->_is_i18n = $evt->_is_i18n;

        // Traitement sur la réception d'un ACK
        if ($dom_evt instanceof LinkACK) {
            // Récupération des données du segment MSH
            $data            = $dom_evt->getMSHEvenementXML();
            $data["msg_hl7"] = $msg;
            $dom_evt->handle(null, null, $data);

            return null;
        }

        try {
            // Création de l'échange
            $exchange_hl7v2 = new CExchangeHL7v2();
            $exchange_hl7v2->load($data_format->_exchange_id);

            // Récupération des données du segment MSH
            $data                                 = $dom_evt->getMSHEvenementXML();
            $exchange_hl7v2->identifiant_emetteur = $data['identifiantMessage'];

            // Gestion de l'acquittement
            $ack                     = $dom_evt->getEventACK($evt);
            $ack->message_control_id = $data['identifiantMessage'];

            // Message non supporté pour cet utilisateur
            $evt_class = CHL7Event::getEventClass($evt);
            if (!in_array($evt_class, $data_format->_messages_supported_class)) {
                $data_format->_ref_sender->_delete_file = false;
                // Pas de création d'échange dans le cas :
                // * où l'on ne souhaite pas traiter le message
                // * où le sender n'enregistre pas les messages non pris en charge
                if (!$data_format->_to_treatment || !$data_format->_ref_sender->save_unsupported_message) {
                    return null;
                }

                $exchange_hl7v2->populateExchange($data_format, $evt);
                $exchange_hl7v2->loadRefsInteropActor();
                $exchange_hl7v2->populateErrorExchange(null, $evt);

                $ack->_ref_exchange_hl7v2 = $exchange_hl7v2;
                $msgAck                   = $ack->generateAcknowledgment("AR", "E001", "201");

                $exchange_hl7v2->populateErrorExchange($ack);

                return $msgAck;
            }

            $sender = $data_format->_ref_sender;
            $sender->getConfigs($data_format);

            // Acquittement d'erreur d'un document XML recu non valide
            if (isset($sender->_configs["bypass_validating"]) && !$sender->_configs["bypass_validating"]
                && !$evt->message->isOK(CHL7v2Error::E_ERROR)
            ) {
                $exchange_hl7v2->populateExchange($data_format, $evt);
                $exchange_hl7v2->loadRefsInteropActor();
                $exchange_hl7v2->populateErrorExchange(null, $evt);
                if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) {
                    CSenderHL7v2Doctolib::storeLastEvent($exchange_hl7v2);
                }

                $ack->_ref_exchange_hl7v2 = $exchange_hl7v2;
                $msgAck                   = $ack->generateAcknowledgment("AR", "E002", "207");

                $exchange_hl7v2->populateErrorExchange($ack);

                return $msgAck;
            }

            $exchange_hl7v2->populateExchange($data_format, $evt);
            $exchange_hl7v2->message_valide = 1;

            // Gestion des notifications ?
            if (!$exchange_hl7v2->_id) {
                $exchange_hl7v2->date_production = CMbDT::dateTime();
            }

            $exchange_hl7v2->store();

            // Pas de traitement du message
            if (!$data_format->_to_treatment) {
                return null;
            }

            $exchange_hl7v2->loadRefsInteropActor();

            // Chargement des configs de l'expéditeur
            $sender = $exchange_hl7v2->_ref_sender;
            $sender->getConfigs($data_format);

            if (!$dom_evt->checkApplicationAndFacility($data, $sender)) {
                return null;
            }

            if (!empty($sender->_configs["handle_mode"])) {
                CHL7v2Message::setHandleMode($sender->_configs["handle_mode"]);
            }

            $dom_evt->_ref_exchange_hl7v2 = $exchange_hl7v2;
            $ack->_ref_exchange_hl7v2     = $exchange_hl7v2;

            // Message PAM / DEC / PDQ / SWF
            $msgAck = self::handleEvent($exchange_hl7v2, $dom_evt, $ack, $data);

            CHL7v2Message::resetBuildMode();
        } catch (Exception $e) {
            $exchange_hl7v2->populateExchange($data_format, $evt);
            if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) {
                CSenderHL7v2Doctolib::storeLastEvent($exchange_hl7v2);
            }

            $exchange_hl7v2->loadRefsInteropActor();
            $exchange_hl7v2->populateErrorExchange(null, $evt);

            $ack                      = new CHL7v2Acknowledgment($evt);
            $ack->message_control_id  = isset($data['identifiantMessage']) ? $data['identifiantMessage'] : "000000000";
            $ack->_ref_exchange_hl7v2 = $exchange_hl7v2;
            $msgAck                   = $ack->generateAcknowledgment("AR", "E003", "207", "E", $e->getMessage());

            $exchange_hl7v2->populateErrorExchange($ack);

            CHL7v2Message::resetBuildMode();
        }

        return $msgAck;
    }

    /**
     * Handle event PAM / DEC / PDQ / SWF message
     *
     * @param CExchangeHL7v2     $exchange_hl7v2 Exchange HL7v2
     * @param CHL7v2MessageXML   $dom_evt        DOM Event
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array              $data           Nodes data
     *
     * @return null|string
     */
    static function handleEvent(
        CExchangeHL7v2 $exchange_hl7v2,
        CHL7v2MessageXML $dom_evt,
        CHL7Acknowledgment $ack,
        $data = []
    ) {
        $patient                             = new CPatient();
        $patient->_eai_exchange_initiator_id = $exchange_hl7v2->_id;

        $data = array_merge($data, $dom_evt->getContentNodes());

        return $dom_evt->handle($ack, $patient, $data);
    }
}
