<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Ox\Core\CMbXPath;

/**
 * Class CHL7v3AcknowledgmentRetrieveDocumentSet
 * Acknowledgment XDS
 */
class CHL7v3AcknowledgmentRetrieveDocumentSet extends CHL7v3AcknowledgmentXDSb {

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    $dom = $this->dom;

    $this->xpath = $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("rs", "urn:oasis:names:tc:ebxml-regrep:xsd:rs:3.0");
    $xpath->registerNamespace("xds", "urn:ihe:iti:xds-b:2007");
    $xpath->registerNamespace("xop", "http://www.w3.org/2004/08/xop/include");

    $status = $xpath->queryAttributNode("/rs:RegistryResponse", null, "status");
    $this->status = substr($status, strrpos($status, ":")+1);
    return $this->status;
  }

  /**
   * Get query ack
   *
   * @return string[]
   */
  function getQueryAck() {
    $xpath = $this->xpath;
    $status = $this->status;
    if ($status === "Failure") {
      $nodes = $xpath->query("//rs:RegistryErrorList/rs:RegistryError");
      $ack = array();
      foreach ($nodes as $_node) {
        $ack[] = array("status"  => $xpath->queryAttributNode(".", $_node, "codeContext"),
                       "context" => $xpath->queryAttributNode(".", $_node, "errorCode")
        );
      }
    }
    else {
      $nodes = $xpath->query("//xds:DocumentResponse");

      $ack = array();
      foreach ($nodes as $_node) {
        $ack[] = array("oid"        => $xpath->queryTextNode("./xds:DocumentUniqueId", $_node),
                       "xop_id"     => $xpath->queryAttributNode("./xds:Document/xop:Include", $_node, "href"),
                       "repository" => $xpath->queryTextNode("./xds:RepositoryUniqueId", $_node),
        );
      }
    }

    return $ack;
  }
}