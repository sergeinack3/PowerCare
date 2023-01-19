<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use DOMElement;
use DOMNode;
use Ox\Core\CMbXPath;

/**
 * Class CHL7v3AcknowledgmentXDSb
 * Acknowledgment XDS
 */
class CHL7v3AcknowledgmentXDSb extends CHL7v3EventXDSb {
  public $acknowledgment;
  public $status;
  /** @var  CMbXPath */
  public $xpath;

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    $dom = $this->dom;
    $this->xpath = $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("rs", "urn:oasis:names:tc:ebxml-regrep:xsd:rs:3.0");
    $xpath->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");
    $status = $xpath->queryAttributNode(".", null, "status");
    $this->status  = substr($status, strrpos($status, ":")+1);
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
      $nodes = $xpath->query("//rim:RegistryObjectList/rim:ObjectRef");
      if ($nodes && $nodes->length > 0) {
        $ack = array();
        foreach ($nodes as $_node) {
          $ack[] = array("status"  => $xpath->queryAttributNode(".", $_node, "id"),
                         "context" => "");
        }
      }
      else {
        $ack[] = array("status" => $status, "context" => "");
      }
    }

    return $ack;
  }

  /**
   * Return the result count
   *
   * @return string
   */
  function getResultCount() {
    return $this->xpath->queryAttributNode(".", null, "totalResultCount");
  }

  /**
   * Get the entryUUID of document in the association
   *
   * @return String[]
   */
  function getDocumentUUIDAssociation() {
    $xpath     = $this->xpath;
    $nodes     = $xpath->query("//rim:Association");
    $entryUUID = array();

    foreach ($nodes as $_node) {
      $type = $xpath->queryAttributNode(".", $_node, "associationType");

      if ($type != "urn:oasis:names:tc:ebxml-regrep:AssociationType:HasMember") {
        continue;
      }
      $entryUUID[] = $xpath->queryAttributNode(".", $_node, "targetObject");
    }

    return $entryUUID;
  }

  /**
   * Return the extrinsic object
   *
   * @return DOMElement[]
   */
  function getDocuments() {
    $xpath     = $this->xpath;
    $nodes     = $xpath->query("//rim:ExtrinsicObject");

    $documents = array();
    foreach ($nodes as $_node) {
      $documents[] = $_node;
    }

    return $documents;
  }

  /**
   * Return the extrinsic object
   *
   * @return string
   */
  function getUUID() {
    $xpath = $this->xpath;
    $nodes = $xpath->query("//rim:ObjectRef");

    $uuid = array();
    /** @var DOMNode $_node */
    foreach ($nodes as $_node) {
      $uuid[] = $xpath->query("@id", $_node)->item(0)->nodeValue;
    }

    return $uuid[0];
  }

  /**
   * Return the last extrinsic object
   *
   * @return string
   */
  function getLastUUID() {
    $xpath = $this->xpath;
    $nodes = $xpath->query("//rim:ObjectRef");

    $uuid = array();
    /** @var DOMNode $_node */
    foreach ($nodes as $_node) {
      $uuid[] = $xpath->query("@id", $_node)->item(0)->nodeValue;
    }

    return end($uuid);
  }

  /**
   * Get metadata for document and put them in session
   *
   * @param String   $namespace       namespace
   * @param String   $uuid            uuid
   *
   * @return array
   */
  function getMetadataDocument($namespace, $uuid = null) {
    $xpath           = $this->xpath;
    $extrinsicObject = $xpath->getNode("//$namespace:ExtrinsicObject[@lid='$uuid']");

    $metadata = array();
    if (!$extrinsicObject) {
        return $metadata;
    }

    // hash
    $metadata["hash"]         = $xpath->queryTextNode("$namespace:Slot[@name='hash']", $extrinsicObject);
    // creationTime
    //$metadata["creationTime"] = $xpath->queryTextNode("$namespace:Slot[@name='creationTime']", $extrinsicObject);
    // size
    $metadata["size"] = $xpath->queryTextNode("$namespace:Slot[@name='size']", $extrinsicObject);
    // repositoryUniqueId
    $metadata["repositoryUniqueId"] = $xpath->queryTextNode("$namespace:Slot[@name='repositoryUniqueId']", $extrinsicObject);
    // version
    $node_version = $xpath->getNode("$namespace:VersionInfo", $extrinsicObject);
    $metadata["version"] = $xpath->getValueAttributNode($node_version, "versionName");
    // extrinsicNode
    $metadata["extrinsicNode"] = $extrinsicObject;

    return $metadata;
  }
}