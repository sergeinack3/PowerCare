<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Webservices\CSenderSOAP;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * The COperatorHprimXML class
 */
class COperatorHprimXML extends CEAIOperator {
  /**
   * Event dispatch
   *
   * @param CExchangeDataFormat $data_format Exchange data format
   *
   * @throws CMbException
   *
   * @return string Acquittement
   */
  function event(CExchangeDataFormat $data_format) {
    $msg               = $data_format->_message;
    $data_format->loadRefsInteropActor();
    $sender = $data_format->_ref_sender;

    /** @var CHPrimXML $evt */
    $evt                 = $data_format->_event_message;
      $evt->_data_format = $data_format;

    $safe = false;
    /** @var CSenderSOAP $sender */
    $sender->loadBackRefConfigHprimXML();
    if ($sender->_ref_config_hprim->handle_appFine) {
      if ($sender->_ref_config_hprim->encoding == "UTF-8") {
        $msg = utf8_decode($msg);
      }

      $safe = true;
    }

    /** @var CHPrimXMLEvenements $dom_evt */
    $dom_evt = $evt->getHPrimXMLEvenements($msg, $safe);

    $dom_evt_class = CClassMap::getSN($dom_evt);
    if (!in_array($dom_evt_class, $data_format->_messages_supported_class)) {
      throw new CMbException(CAppUI::tr("CEAIDispatcher-no_message_supported_for_this_actor", $dom_evt_class));
    }
    
    // R�cup�ration du noeud racine
    $root     = $dom_evt->documentElement;
    $nodeName = $root->nodeName;

    // Cr�ation de l'�change
    $echg_hprim = new CEchangeHprim();

    $sender->getConfigs($data_format);
    $dom_evt->_ref_sender = $sender;

    $data = array();
    try {
      $echg_hprim->load($data_format->_exchange_id);

      // R�cup�ration des donn�es de l'ent�te
      $data = $dom_evt->getEnteteEvenementXML($nodeName);

      // Gestion des notifications ?
      if (!$echg_hprim->_id) {
        $echg_hprim->populateEchange($data_format, $dom_evt);
        $echg_hprim->identifiant_emetteur = $data['identifiantMessage'];
        $echg_hprim->message_valide       = 1;
      }

      $echg_hprim->loadRefsInteropActor();

      // Chargement des configs de l'exp�diteur
      $echg_hprim->_ref_sender->getConfigs($data_format);

      $configs = $echg_hprim->_ref_sender->_configs;

      $display_errors = isset($configs["display_errors"]) ? $configs["display_errors"] : true;
      $doc_errors = $dom_evt->schemaValidate(null, false, $display_errors);
  
      // Gestion de l'acquittement
      $dom_acq                        = CHPrimXMLAcquittements::getAcquittementEvenementXML($dom_evt);
      $dom_acq->_identifiant_acquitte = $data['identifiantMessage'];
      $dom_acq->_sous_type_evt        = $dom_evt->sous_type;

      // Acquittement d'erreur d'un document XML recu non valide
      if ($doc_errors !== true) {
        $echg_hprim->populateEchange($data_format, $dom_evt);

        $dom_acq->_ref_echange_hprim = $echg_hprim;
        $msgAcq    = $dom_acq->generateAcquittements(
          $dom_acq instanceof CHPrimXMLAcquittementsServeurActivitePmsi ? "err" : "erreur", "E002", $doc_errors
        );
        $doc_valid = $dom_acq->schemaValidate(null, false, $display_errors);

        $echg_hprim->populateErrorEchange($msgAcq, $doc_valid, "erreur");

        return $msgAcq;
      }
      
      $echg_hprim->date_production = CMbDT::dateTime();
      $echg_hprim->store();
      
      if (!$data_format->_to_treatment) {
        return null;
      }

      $dom_evt->_ref_echange_hprim = $echg_hprim;
      $dom_acq->_ref_echange_hprim = $echg_hprim;
    
      // Message �v�nement patient
      if ($dom_evt instanceof CHPrimXMLEvenementsPatients) {
        return self::eventPatient($dom_evt, $dom_acq, $echg_hprim, $data);
      }
      // Message serveur Frais Divers
      if ($dom_evt instanceof CHPrimXMLEvenementsFraisDivers) {
        return self::eventFraisDivers($dom_evt, $dom_acq, $echg_hprim, $data);
      }
      // Message serveur activit� PMSI
      if ($dom_evt instanceof CHPrimXMLEvenementsServeurActivitePmsi) {
        return self::eventPMSI($dom_evt, $dom_acq, $echg_hprim, $data);
      }
    } 
    catch(Exception $e) {
      if (!$echg_hprim->_id) {
        $echg_hprim->populateEchange($data_format, $dom_evt);
        $echg_hprim->identifiant_emetteur =  isset($data['identifiantMessage']) ? $data['identifiantMessage'] : "000000000";
        $echg_hprim->message_valide       = 1;
      }

      $dom_acq = CHPrimXMLAcquittements::getAcquittementEvenementXML($dom_evt);
      
      // Type par d�faut
      $dom_acq->_sous_type_evt        = "none";
      $dom_acq->_identifiant_acquitte = isset($data['identifiantMessage']) ? $data['identifiantMessage'] : "000000000";
      $dom_acq->_ref_echange_hprim    = $echg_hprim;

      $msgAcq = $dom_acq->generateAcquittements(
        $dom_acq instanceof CHPrimXMLAcquittementsServeurActivitePmsi ? "err" : "erreur", "E009", $e->getMessage(), null, $data
      );

      $doc_valid = $dom_acq->schemaValidate(null, false, false);
      $echg_hprim->populateErrorEchange($msgAcq, $doc_valid, "erreur");
      
      return $msgAcq;
    }

    return null;
  }
  
  /**
   * The message contains a collection of administrative notifications of events occurring to patients in a healthcare 
   * facility.
   *
   * @param CHPrimXMLEvenementsPatients    $dom_evt    DOM event PMSI
   * @param CHPrimXMLAcquittementsPatients $dom_acq    DOM acquittement PMSI
   * @param CEchangeHprim                  $echg_hprim Exchange H'XML
   * @param array                          &$data      Data
   * 
   * @return string Acquittement
   **/
  static function eventPatient(CHPrimXMLEvenementsPatients $dom_evt, CHPrimXMLAcquittementsPatients $dom_acq,
      CEchangeHprim $echg_hprim, &$data = array()
  ) {
    $newPatient = new CPatient();
    $newPatient->_eai_exchange_initiator_id = $echg_hprim->_id;
    
    $data = array_merge($data, $dom_evt->getContentsXML());
    if ($msgAcq = $dom_evt->isActionValide($data['action'], $dom_acq, $echg_hprim)) {
      return $msgAcq;
    }

    // Un �v�nement concernant un patient appartient � l'une des six cat�gories suivantes :
    switch (get_class($dom_evt)) {
      // Enregistrement d'un patient avec son identifiant (ipp) dans le syst�me
      case CHPrimXMLEnregistrementPatient::class:
        /** @var CHPrimXMLEnregistrementPatient $dom_evt */
        $echg_hprim->id_permanent = $data['idSourcePatient'];

        return $dom_evt->enregistrementPatient($dom_acq, $newPatient, $data);

      // Fusion de deux ipp
      case CHPrimXMLFusionPatient::class:
        /** @var CHPrimXMLFusionPatient $dom_evt */
        $echg_hprim->id_permanent = $data['idSourcePatient'];

        return $dom_evt->fusionPatient($dom_acq, $newPatient, $data);

      // Venue d'un patient dans l'�tablissement avec son num�ro de venue
      case CHPrimXMLVenuePatient::class:
        /** @var CHPrimXMLVenuePatient $dom_evt */
        $echg_hprim->id_permanent = $data['idSourceVenue'];

        return $dom_evt->venuePatient($dom_acq, $newPatient, $data);

      // Fusion de deux venues
      case CHPrimXMLFusionVenue::class:
        /** @var CHPrimXMLFusionVenue $dom_evt */
        $echg_hprim->id_permanent = $data['idSourceVenue'];

        return $dom_evt->fusionVenue($dom_acq, $newPatient, $data);

      // Mouvement du patient dans une unit� fonctionnelle ou m�dicale
      case CHPrimXMLMouvementPatient::class:
        /** @var CHPrimXMLMouvementPatient $dom_evt */
        $echg_hprim->id_permanent = $data['idSourceVenue'];

        return $dom_evt->mouvementPatient($dom_acq, $newPatient, $data);

        // Gestion des d�biteurs d'une venue de patient
      case CHPrimXMLDebiteursVenue::class:
        /** @var CHPrimXMLDebiteursVenue $dom_evt */
        $echg_hprim->id_permanent = $data['idSourcePatient'];

        return $dom_evt->debiteursVenue($dom_acq, $newPatient, $data);

      default:
        return $dom_acq->generateAcquittements("erreur", "E007");
    }
  }
  
  /**
   * The message contains a collection of 
   * 
   * @param CHPrimXMLEvenementsServeurActivitePmsi    $dom_evt    DOM event PMSI
   * @param CHPrimXMLAcquittementsServeurActivitePmsi $dom_acq    DOM acquittement PMSI
   * @param CEchangeHprim                             $echg_hprim Exchange H'XML
   * @param array                                     &$data      Data
   * 
   * @return string Acquittement 
   **/
  static function eventPMSI(CHPrimXMLEvenementsServeurActivitePmsi $dom_evt, CHPrimXMLAcquittementsServeurActivitePmsi $dom_acq,
      CEchangeHprim $echg_hprim, &$data = array()
  ) {
    $data   = array_merge($data, $dom_evt->getContentsXML());
    if (CMbArray::get($data, "action") && $msgAcq = $dom_evt->isActionValide($data['action'], $dom_acq, $echg_hprim)) {
      return $msgAcq;
    } 
    
    $operation = new COperation();
    $operation->_eai_exchange_initiator_id = $echg_hprim->_id;
    
    return $dom_evt->handle($dom_acq, $operation, $data);
  }

  /**
   * The message contains a collection of
   *
   * @param CHPrimXMLEvenementsFraisDivers    $dom_evt    DOM event PMSI
   * @param CHPrimXMLAcquittementsFraisDivers $dom_acq    DOM acquittement PMSI
   * @param CEchangeHprim                     $echg_hprim Exchange H'XML
   * @param array                             &$data      Data
   *
   * @return string Acquittement
   **/
  static function eventFraisDivers(CHPrimXMLEvenementsFraisDivers $dom_evt, CHPrimXMLAcquittementsFraisDivers $dom_acq,
                            CEchangeHprim $echg_hprim, &$data = array()
  ) {
    $data   = array_merge($data, $dom_evt->getContentsXML());
    if (CMbArray::get($data, "action") && $msgAcq = $dom_evt->isActionValide($data['action'], $dom_acq, $echg_hprim)) {
      return $msgAcq;
    }

    $sejour = new CSejour();
    $sejour->_eai_exchange_initiator_id = $echg_hprim->_id;

    return $dom_evt->handle($dom_acq, $sejour, $data);
  }
}
