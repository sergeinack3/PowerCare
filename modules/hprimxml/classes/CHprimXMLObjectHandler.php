<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CMbObject;
use Ox\Core\Handlers\ObjectHandler;

/**
 * Class CHprimXMLObjectHandler
 */
class CHprimXMLObjectHandler  extends ObjectHandler {
  static $handled = array ();

  /**
   * Génération de l'évènement
   *
   * @param string    $evenement  Évènement H'XML
   * @param CMbObject $mbObject   Object
   * @param bool      $referent   Référent ?
   * @param bool      $initiateur Initiateur du message ?
   *
   * @return void
   */
  function generateTypeEvenement($evenement, CMbObject $mbObject, $referent = null, $initiateur = null) {
    /** @var CDestinataireHprim $receiver */
    $receiver = $mbObject->_receiver;
    
    if (!$receiver->isMessageSupported($evenement)) {
      return;
    }

    /** @var CHPrimXMLEvenements $dom */
    $dom = new $evenement;
    $dom->_ref_receiver = $receiver;
    $dom->generateTypeEvenement($mbObject, true, $initiateur);
  }

  /**
   * Send event patient
   *
   * @param string    $evenement Event
   * @param CMbObject $mbObject  Object
   *
   * @return void
   */
  function sendEvenementPatient($evenement, CMbObject $mbObject) {
    /** @var CDestinataireHprim $receiver */
    $receiver = $mbObject->_receiver;
    
    if (!$receiver->isMessageSupported($evenement)) {
      return;
    }
    
    $dom = new $evenement;
    $dom->_ref_receiver = $receiver;
    $receiver->sendEvenementPatient($dom, $mbObject);
  }

  function sendEvenementPresta($evenement, CMbObject $mbObject) {
    /** @var CDestinataireHprim $receiver */
    $receiver = $mbObject->_receiver;

    if (!$receiver->isMessageSupported($evenement)) {
      return;
    }

    $dom = new $evenement;
    $dom->_ref_receiver = $receiver;
    $receiver->sendEvenementPresta($dom, $mbObject);
  }

  /**
   * Send event PMSI
   *
   * @param string    $evenement Event
   * @param CMbObject $mbObject  Object
   *
   * @return void
   */
  function sendEvenementPMSI($evenement, CMbObject $mbObject) {
    /** @var CDestinataireHprim $receiver */
    $receiver = $mbObject->_receiver;
    
    if (!$receiver->isMessageSupported($evenement)) {
      return;
    }

    $dom = new $evenement;
    $dom->_ref_receiver = $receiver;
    $receiver->sendEvenementPMSI($dom, $mbObject);
  }
}