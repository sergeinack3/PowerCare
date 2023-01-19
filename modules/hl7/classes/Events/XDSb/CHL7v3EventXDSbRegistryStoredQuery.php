<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Interop\Xds\CXDSQueryRegistryStoredQuery;

/**
 * CHL7v3EventXDSRegistryStoredQuery
 * Registry stored query
 */
class CHL7v3EventXDSbRegistryStoredQuery extends CHL7v3EventXDSb implements CHL7EventXDSbRegistryStoredQuery {
  /** @var string */
  public $interaction_id = "RegistryStoredQuery";

  /**
   * Build ProvideAndRegisterDocumentSetRequest event
   *
   * @param CXDSQueryRegistryStoredQuery $object compte rendu
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    //Récupération de l'objet mediboard concerné par la requête à effectuer(présence du loadLastLog dans le parent)
    $mb_object = $object->document_item ?: $object->patient;

    parent::build($mb_object);

    $this->message = $object->createQuery()->saveXML();

    $this->updateExchange(false);
  }

  /**
   * @see parent::getAcknowledgment
   */
  function getAcknowledgment() {

    $dom = new CMbXMLDocument();
    $dom->loadXML($this->ack_data);

    $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("rs", "urn:oasis:names:tc:ebxml-regrep:xsd:rs:3.0");
    $xpath->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");
    $status = $xpath->queryAttributNode(".", null, "status");

    if ($status === "urn:oasis:names:tc:ebxml-regrep:ResponseStatusType:Failure") {
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
      $ack = array();
      foreach ($nodes as $_node) {
        $ack[] = array("status"  => $xpath->queryAttributNode(".", $_node, "id"),
                       "context" => "");
      }
    }

    return $ack;
  }
}
