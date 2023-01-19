<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Webservices\CEchangeSOAP;
use Ox\Interop\Webservices\CSenderSOAP;
use Ox\Interop\Webservices\CSoapHandler;
use Ox\Mediboard\Admin\CUser;

/**
 * Class CEAISoapHandler
 * EAI SOAP Handler
 */

class CEAISoapHandler extends CSoapHandler {
  /**
   * Params specs
   *
   * @var array
   */
  static $paramSpecs = array(
    "event" => array(
      "parameters" => array(
        "message" => "string"
      ),
      "return" => array(
        "response" => "string"
      )
    )
  );
  
  /**
   * Get parameters specifications
   * 
   * @return array
   */
  static function getParamSpecs() {
    return array_merge(parent::getParamSpecs(), self::$paramSpecs);
  }
  
  /**
   * Event method
   * 
   * @param string $message  Message
   * @param int    $actor_id Actor id
   * 
   * @return string ACK
   */ 
  function event($message, $actor_id = null) {
    CApp::$chrono->stop();
    $chrono = new Chronometer();
    $chrono->start();

    $sender_soap = new CSenderSOAP();
    if ($actor_id) {
      $sender_soap->load($actor_id);
    }
    else {
      $sender_soap->user_id = CUser::get()->_id;
      $sender_soap->role    = CAppUI::conf("instance_role");
      $sender_soap->actif   = "1";
      $sender_soap->loadMatchingObject();
    }

    $echange_soap = new CEchangeSOAP();

    $echange_soap->date_echange = "now";
    $echange_soap->emetteur     = $sender_soap->_id ? $sender_soap->nom : CAppUI::tr("Unknown");
    $echange_soap->destinataire = CAppUI::conf("mb_id");
    $echange_soap->type         = CMbArray::get($_REQUEST, "class");

    if ($request_uri = CMbArray::get($_SERVER, "REQUEST_URI")) {
      $url                            = parse_url($request_uri);
      $echange_soap->web_service_name = CMbArray::get($url, "query");
    }

    $echange_soap->function_name = CMbArray::get($_SERVER, "HTTP_SOAPACTION");

    $echange_soap->input = serialize($message);

    if (CAppUI::conf("webservices CSoapHandler loggable", $sender_soap->_id ? "CGroups-$sender_soap->group_id" : "global")) {
      $echange_soap->store();
    }

    if (!$sender_soap->_id) {
      CEAIDispatcher::$errors[] = CAppUI::tr("CEAISoapHandler-no_actor");
      CEAIDispatcher::dispatchError($message);

      $chrono->stop();
      $echange_soap->date_echange  = "now";
      $echange_soap->output        = CEAIDispatcher::$error;
      $echange_soap->soapfault     = 1;
      $echange_soap->response_time = $chrono->total;
      $echange_soap->store();

      CApp::$chrono->start();

      return CEAIDispatcher::$error;
    }

    $actor = null;
    if ($sender_soap->_id) {
      $actor = $sender_soap;
    }

    // Dispatch EAI 
    if (!$acq = CEAIDispatcher::dispatch($message, $actor)) {
      $acq = utf8_encode(CEAIDispatcher::$error);
    }

    $chrono->stop();
    $echange_soap->date_echange  = CMbDT::dateTime();
    $echange_soap->output        = serialize($acq);
    $echange_soap->response_time = $chrono->total;
    $echange_soap->store();

    CApp::$chrono->start();

    return $acq;
  }
}
