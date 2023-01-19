<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Exception;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class COperatorHPrimSante
 * Operator Hprim sante
 */
class COperatorHPrimSante extends CEAIOperator {
  /**
   * Event
   *
   * @param CExchangeDataFormat $data_format data format
   *
   * @return $this|CHPrimSanteAcknowledgment|null|void
   */
  function event(CExchangeDataFormat $data_format) {
    $msg               = $data_format->_message;
    /** @var CHPrimSanteEvent $evt */
    $evt                 = $data_format->_event_message;
      $evt->_data_format = $data_format;

    // Récupération des informations du message
    /** @var CHPrimSanteMessageXML $dom_evt */
    $dom_evt = $evt->handle($msg);

    try {
      // Création de l'échange
      $exchange_hpr = new CExchangeHprimSante();
      $exchange_hpr->load($data_format->_exchange_id);

      $exchange_hpr->_event_message = $data_format->_event_message;

      // Récupération des données du segment H
      $data = $dom_evt->getHEvenementXML();

      // Gestion de l'acquittement
      $ack = new CHPrimSanteAcknowledgment($evt);

      $evt_class = CHPrimSanteEvent::getEventClass($evt);
      if (!in_array($evt_class, $data_format->_messages_supported_class)) {
        $data_format->_ref_sender->_delete_file = false;
        // Pas de création d'échange dans le cas :
        // * où l'on ne souhaite pas traiter le message
        // * où le sender n'enregistre pas les messages non pris en charge
        if (!$data_format->_to_treatment || !$data_format->_ref_sender->save_unsupported_message) {
          return null;
        }

        $exchange_hpr->populateExchange($data_format, $evt);
        $exchange_hpr->loadRefsInteropActor();

        $ack->_ref_exchange_hpr = $exchange_hpr;
        $error = new CHPrimSanteError($exchange_hpr, "T", "09", array("P", 1, array("")), "8.1");
        $ack = $ack->generateAcknowledgment(array($error));

        $exchange_hpr->populateExchangeACK($ack);

        return $ack;
      }

      // Acquittement d'erreur d'un document XML recu non valide
      $exchange_hpr->populateExchange($data_format, $evt);
      $exchange_hpr->message_valide = 1;

      // Gestion des notifications ?
      if (!$exchange_hpr->_id) {
        $exchange_hpr->date_production      = $data['dateHeureProduction'];
        $exchange_hpr->nom_fichier          = $data['filename'];
      }

      $exchange_hpr->store();

      // Pas de traitement du message
      if (!$data_format->_to_treatment) {
        return null;
      }

      $exchange_hpr->loadRefsInteropActor();

      // Chargement des configs de l'expéditeur
      $sender = $exchange_hpr->_ref_sender;
      $sender->getConfigs($data_format);

      $dom_evt->_ref_exchange_hpr = $exchange_hpr;
      $dom_evt->_ref_sender       = $sender;
      $ack->_ref_exchange_hpr     = $exchange_hpr;

      // Message ADM / REG
      $ack = self::handleEvent($data, $exchange_hpr, $dom_evt, $ack);
    } catch(Exception $e) {
      $exchange_hpr->populateExchange($data_format, $evt);
      $exchange_hpr->loadRefsInteropActor();

      $ack = new CHPrimSanteAcknowledgment($evt);
      $ack->_ref_exchange_hpr = $exchange_hpr;
      $error = new CHPrimSanteError($exchange_hpr, "T", "09", array("P", 1, array("")), "8.1", $e->getMessage());
      $ack = $ack->generateAcknowledgment(array($error));
      $exchange_hpr->populateExchangeACK($ack);
    }

    return $ack;
  }

  /**
   * handle event
   *
   * @param array                     $data         data
   * @param CExchangeHprimSante       $exchange_hpr exchange
   * @param CHPrimSanteMessageXML     $dom_evt      event xml
   * @param CHPrimSanteAcknowledgment $ack          Acknowledgment
   *
   * @return CHPrimSanteAcknowledgment
   */
  static function handleEvent(
    $data, CExchangeHprimSante $exchange_hpr,
    CHPrimSanteMessageXML $dom_evt, CHPrimSanteAcknowledgment $ack
  ) {
    $data = array_merge($data, $dom_evt->getContentNodes());

    $object = new CPatient();

    return $dom_evt->handle($ack, $object, $data);
  }
}

