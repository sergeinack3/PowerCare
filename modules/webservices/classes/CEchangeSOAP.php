<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\CAppUI;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * Class CEchangeSOAP
 * Exchange SOAP
 */
class CEchangeSOAP extends CExchangeTransportLayer {
  // DB Table key
  public $echange_soap_id;
  
  // DB Fields
  public $type;
  public $web_service_name;
  public $soapfault;
  public $trace;
  public $last_request_headers;
  public $last_response_headers;
  public $last_request;
  public $last_response;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->loggable = false;
    $spec->table = 'echange_soap';
    $spec->key   = 'echange_soap_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["source_class"]          = "enum list|CSourceSOAP";
    $props["source_id"]            .= " back|echange_soap";
    $props["type"]                  = "str";
    $props["web_service_name"]      = "str";
    $props["soapfault"]             = "bool";
    $props["trace"]                 = "bool";
    $props["last_request_headers"]  = "text show|0";
    $props["last_response_headers"] = "text show|0";
    $props["last_request"]          = "xml show|0";
    $props["last_response"]         = "xml show|0";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function unserialize() {
    $this->input  = unserialize($this->input);
    if ($this->soapfault != 1) {
      $this->output = unserialize($this->output);
    }
  }

  /**
   * @inheritdoc
   */
  function fillDownloadExchange() {
    $content = parent::fillDownloadExchange();

    $output = $this->soapfault ? print_r($this->output, true) : print_r((@unserialize($this->output) !== false) ? : $this->output, true);
    $content .= CAppUI::tr("{$this->_class}-output") . " : {$output} \n \n";

    if (CAppUI::conf("webservices trace")) {
      $content .= CAppUI::tr("{$this->_class}-last_request_headers") . " : {$this->last_request_headers} \n \n";
      $content .= CAppUI::tr("{$this->_class}-last_request") . " : {$this->last_request} \n \n";
      $content .= CAppUI::tr("{$this->_class}-last_response_headers") . " : {$this->last_response_headers} \n \n";
      $content .= CAppUI::tr("{$this->_class}-last_response") . " : {$this->last_response} \n \n";
    }

    return $content;
  }
}