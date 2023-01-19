<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CAppUI;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * HTTP exchange
 */
class CExchangeHTTP extends CExchangeTransportLayer {
  public $echange_http_id;

  // DB Fields
  public $http_fault;
  public $status_code;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->loggable = false;
    $spec->table    = 'echange_http';
    $spec->key      = 'echange_http_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["http_fault"]   = "bool";
    $props["status_code"]  = "num";
    $props["source_class"] = "enum list|CSourceHTTP";
    $props["source_id"] .= " cascade back|echange_http";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function fillDownloadExchange() {
    $content = parent::fillDownloadExchange();

    $output  = print_r($this->output, true);
    $content .= CAppUI::tr("{$this->_class}-output") . " : {$output} \n \n";

    return $content;
  }

  /**
   * Get input data
   *
   * @return string|null
   */
  static function getInputData() {
    switch (strtolower($_SERVER['REQUEST_METHOD'])) {
      case "get":
      case "delete":
        return $_SERVER["REQUEST_URI"] . "?" . $_SERVER["QUERY_STRING"];
        break;

      case "put":
      case "post":
        $headers = getallheaders();
        array_walk(
          $headers,
          function (&$value, $key) {
            $value = "$key: $value";
          }
        );

        $str = implode("\n", $headers);

        return $str . "\r\n\r\n" . file_get_contents('php://input');
        break;

      default:
        return null;
    }
  }

  /**
   * @return false|string
   */
  static function getPHPInput() {
    return file_get_contents('php://input');
  }
}