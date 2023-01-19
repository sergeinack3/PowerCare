<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Tunnel HTTP class
 */
class CHTTPTunnelObject extends CMbObject {
  /** @var integer Primary key */
  public $http_tunnel_id;
  public $address;
  public $status;
  public $start_date;
  public $ca_file;

  public $_message_status;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "http_tunnel";
    $spec->key    = "http_tunnel_id";
    return $spec;  
  }

  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["status"]     = "bool notNull default|1";
    $props["address"]    = "str notNull";
    $props["start_date"] = "dateTime";
    $props["ca_file"]    = "str";
    
    return $props;
  }

  /**
   * Return all active tunnel
   *
   * @return CHTTPTunnelObject[]
   */
  function loadActiveTunnel() {
    return $this->loadList(array("status"=>"='1'"), "start_date ASC");
  }

  /**
   * Verify the disponibility of the tunnel
   *
   * @return bool
   */
  function checkStatus() {
    try{
      $http_client = new CHTTPClient($this->address);
      $http_client->setOption(CURLOPT_HEADER, true);
      $http_client->setOption(CURLOPT_TIMEOUT, 10);
      $http_client->setOption(CURLOPT_CUSTOMREQUEST, "CMD TEST");
      if ($this->ca_file) {
        $http_client->setSSLPeer($this->ca_file);
      }
      $result = $http_client->executeRequest();
    }
    catch(Exception $e) {
      $this->_message_status = $e->getMessage();
      $result = "";
    }
    $return = preg_match("#200#", $result) ? "1" : "0";

    if ($this->status !== $return) {
      $this->status = $return;
      if ($return && !$this->start_date) {
        $this->start_date = "now";
      }
      else {
        $this->start_date = "";
      }
      $this->store();
    }

    return $return;
  }
}