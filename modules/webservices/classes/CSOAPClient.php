<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use SoapFault;

/**
 * The factory for the SOAP Clients
 */
class CSOAPClient implements IShortNameAutoloadable {

  /** @var string The type of the client */
  public $type_client;

  /** @var CMbSOAPClient The SOAP client */
  public $client;

  /** @var CSourceSOAP */
  public $_source;

  /**
   * The constructor
   *
   * @param string $type The client type
   *
   * @return self
   */
  function __construct($type = "CMbSOAPClient") {
    $this->type_client = $type;
  }

  /**
   * Calls a SOAP function
   *
   * @param string $function_name  The name of the SOAP function to call
   * @param array  $arguments      An array of the arguments to pass to the function
   * @param array  $options        An associative array of options to pass to the client
   * @param mixed  $input_headers  An array of headers to be sent along with the SOAP request
   * @param array  $output_headers If supplied, this array will be filled with the headers from the SOAP response
   *
   * @throws Exception|SoapFault
   *
   * @return mixed SOAP functions may return one, or multiple values
   */
  public function __soapCall($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
    return $this->call($function_name, $arguments, $options, $input_headers, $output_headers);
  }

  /**
   * Calls a SOAP function
   *
   * @param string $function_name  The name of the SOAP function to call
   * @param array  $arguments      An array of the arguments to pass to the function
   * @param array  $options        An associative array of options to pass to the client
   * @param mixed  $input_headers  An array of headers to be sent along with the SOAP request
   * @param array  $output_headers If supplied, this array will be filled with the headers from the SOAP response
   *
   * @throws Exception|SoapFault
   *
   * @return mixed SOAP functions may return one, or multiple values
   */
  public function call($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
    $client = $this->client;

    if (!is_array($arguments)) {
      $arguments = array($arguments);
    }

    /* @todo Lors d'un appel d'une méthode RPC le tableau $arguments contient un élement vide array( [0] => )
     * posant problème lors de l'appel d'une méthode du WSDL sans argument */
    if (isset($arguments[0]) && empty($arguments[0])) {
      $arguments = array();
    }

    if ($client->flatten && isset($arguments[0]) && !empty($arguments[0])) {
      $arguments = $arguments[0];
    }

    $echange_soap = new CEchangeSOAP();

    $echange_soap->date_echange = CMbDT::dateTime();
    $echange_soap->emetteur     = CAppUI::conf("mb_id");
    $echange_soap->destinataire = $client->wsdl_url;
    $echange_soap->type         = $client->type_echange_soap;
    $echange_soap->source_id    = $this->_source->_id;
    $echange_soap->source_class = $this->_source->_class;

    $url                            = parse_url($client->wsdl_url);
    $path                           = explode("/", $url['path']);
    $echange_soap->web_service_name = end($path);

    $echange_soap->function_name = $function_name;

    // Truncate input and output before storing
    $arguments_serialize = array_map_recursive(array(CSOAPClient::class, "truncate"), $arguments);

    $echange_soap->input = serialize($arguments_serialize);

    if ($client->loggable) {
      $echange_soap->store();
    }

    CApp::$chrono->stop();
    $chrono = new Chronometer();
    $chrono->start();

    $this->_source->startCallTrace();
    try {
      $output = $client->call($function_name, $arguments, $options, $input_headers, $output_headers);
        $this->_source->stopCallTrace();

      if (!$client->loggable) {
        CApp::$chrono->start();

        return $output;
      }
    }
    catch (SoapFault $fault) {
        $this->_source->stopCallTrace();
      // trace
      if (CAppUI::conf("webservices trace")) {
        $client->getTrace($echange_soap);
      }
      $chrono->stop();
      $echange_soap->response_datetime = CMbDT::dateTime();
      $echange_soap->output            = $fault->faultstring;
      $echange_soap->soapfault         = 1;
      $echange_soap->response_time     = $chrono->total;
      $echange_soap->store();

      CApp::$chrono->start();

      throw $fault;
    }

    $chrono->stop();
    CApp::$chrono->start();

    // trace
    if (CAppUI::conf("webservices trace")) {
      $client->getTrace($echange_soap);
    }

    // response time
    $echange_soap->response_time     = $chrono->total;
    $echange_soap->response_datetime = CMbDT::dateTime();

    if ($echange_soap->soapfault != 1) {
      $echange_soap->output = serialize(array_map_recursive(array(CSOAPClient::class, "truncate"), $output));
    }
    $echange_soap->store();

    return $output;
  }

  /**
   * Truncate a string to a given maximum length
   *
   * @param string $string The string to truncate
   *
   * @return string The truncated string
   */
  static public function truncate($string) {
      // todo changer les appels a tous les truncate avec la nouvelle classe (CSourceSoap)
    if (!is_string($string)) {
      return $string;
    }

    // Truncate
    $max    = 1024;
    $result = CMbString::truncate($string, $max);

    // Indicate true size
    $length = strlen($string);
    if ($length > 1024) {
      $result .= " [$length bytes]";
    }

    return $result;
  }
}
