<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Exception;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hprim21\Events\CHPREvent;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class COperatorHPR
 * Operator HPR
 */
class COperatorHPR extends CEAIOperator {
  function event(CExchangeDataFormat $data_format) {
    $msg               = $data_format->_message;
    /** @var CHPREvent $evt */
    $evt                 = $data_format->_event_message;
      $evt->_data_format = $data_format;

    // Récupération des informations du message
    $dom_evt = $evt->handle($msg);

    try {
      // Création de l'échange
      $exchange_hpr = new CEchangeHprim21();
      $exchange_hpr->load($data_format->_exchange_id);
      
      // Récupération des données du segment H
      $data = $dom_evt->getHEvenementXML();

      // Gestion de l'acquittement
      $ack = new CHPrim21Acknowledgment($evt);
      
      $evt_class = CHPREvent::getEventClass($evt);
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
        $exchange_hpr->populateErrorExchange(null, $evt);
        
        $ack->_ref_exchange_hpr = $exchange_hpr;
        $msgAck = $ack->generateAcknowledgment("AR", "E001", "201");
        
        $exchange_hpr->populateErrorExchange($ack);
        
        return $msgAck;
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
      $ack->_ref_exchange_hpr     = $exchange_hpr;

      // Message ADM / REG 
      $msgAck = self::handleEvent($data, $exchange_hpr, $dom_evt, $ack);     
    } catch(Exception $e) {
      
    }

    return $msgAck;
  }
  
  static function handleEvent(
      $data, CEchangeHprim21 $exchange_hpr,
      CHPrim21MessageXML $dom_evt, CHPrim21Acknowledgment $ack
  ) {
    $data = array_merge($data, $dom_evt->getContentNodes());
    
    $object = new CSejour();
    
    return $dom_evt->handle($ack, $object, $data);
  }
}

