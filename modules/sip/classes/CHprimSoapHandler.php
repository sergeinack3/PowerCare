<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sip;

use Ox\Interop\Eai\CEAISoapHandler;
use Ox\Interop\Hprimxml\CHPrimXMLAcquittementsPatients;
use Ox\Interop\Hprimxml\CHPrimXMLEvenementsPatients;
use Ox\Interop\Webservices\CSoapHandler;

/**
 * The CHprimSoapHandler class
 */
class CHprimSoapHandler extends CSoapHandler {
  /**
   * @var array
   */
  static $paramSpecs = array(
    "evenementPatient" => array(
      "parameters" => array(
        "messagePatient" => "string"
      ),
      "return" => array(
        "response" => "string"
      )
    )
  );

  /**
   * Get param specs
   *
   * @return array
   */
  static function getParamSpecs() {
    return array_merge(parent::getParamSpecs(), self::$paramSpecs);
  }

  /**
   * The message contains a collection of administrative notifications of events occurring to patients in a healthcare facility.
   *
   * @param CHPrimXMLEvenementsPatients $messagePatient Message
   *
   * @return CHPrimXMLAcquittementsPatients messageAcquittement 
   **/
  function evenementPatient($messagePatient) {
    $eai_soap_handler = new CEAISoapHandler();
    
    return $eai_soap_handler->event($messagePatient);    
  }
}