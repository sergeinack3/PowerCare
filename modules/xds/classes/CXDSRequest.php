<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Dmp\CDMPXmlDocument;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CHL7v3Adressing;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSbRegistryStoredQuery;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSbRetrieveDocumentSet;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CPatient;

/**
 * Request class
 */
class CXDSRequest implements IShortNameAutoloadable {
  static public $method      = "POST";
  static public $type        = "XDS";
  static public $version     = "HTTP/1.0";
  static public $http_header = array(
    "Host"           => "**MbHost_ip**",
    "Connection"     => "keep-alive",
    "User-Agent"     => "MbHost",
    "Content-Type"   => "text/xml; charset=utf-8",
    "SOAPAction"     => "",
    "Content-Length" => "",
  );

  /**
   * Generate the request
   *
   * @param String $body          Body
   * @param String $soap_action   Soap action
   * @param String $emplacement   url
   * @param int    $contentLength body length
   *
   * @return string
   */
  static function generateRequest($body, $soap_action, $emplacement, $contentLength=null) {
    $request     = self::$method." $emplacement ".self::$version;
    $http_header = self::$http_header;
    $http_header["Content-Length"] = $contentLength ? $contentLength : strlen($body);
    $http_header["SOAPAction"]     = "\"$soap_action\"";

    foreach ($http_header as $_param => $_value) {
      $request .= "\n$_param: $_value";
    }

    $request .= "\r\n\r\n$body";

    return $request;
  }

  /**
   * Send the event ProvideAndRegisterDocumentSetRequest
   *
   * @param CReceiverHL7v3 $receiver_hl7v3 Receiver HL7v3
   * @param CDocumentItem  $document_item  Document
   * @param CXDSDocument   $document_xds   XDS document
   * @param String         $hide           Hide the document
   * @param String         $uuid           Identifiant of older version
   * @param String         $certificat     Certificat
   * @param String         $passphrase     Passphrase
   *
   * @throws Exception
   * @return mixed
   */
  static function sendEventProvideAndRegisterDocumentSetRequest(
      CReceiverHL7v3 $receiver_hl7v3,
      CDocumentItem $document_item,
      CXDSDocument $document_xds,
      $hide,
      $uuid,
      $certificat = null,
      $passphrase = null
  ) {
    $iti41                         = new CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest();
    $iti41->sign                   = false;
    $iti41->passphrase_certificate = $passphrase;
    $iti41->path_certificate       = $certificat;
    $iti41->hide                   = $hide;
    $iti41->uuid                   = $uuid;
    $iti41->old_version            = $document_xds->version;
    $iti41->old_id                 = $document_xds->object_id;
    $iti41->type                   = CXDSRequest::$type;
    $iti41->_event_name            = "ProvideAndRegisterDocumentSetRequest";

    $headers = CHL7v3Adressing::createWSAddressing(
      "urn:ihe:iti:2007:ProvideAndRegisterDocumentSet-b",
      "http://ihexds.nist.gov/tf6/services/xdsrepositoryb"
    );

    return $receiver_hl7v3->sendEvent($iti41, $document_item, $headers, true);
  }

  /**
   * Send the event RegistryStoredQuery
   *
   * @param CReceiverHL7v3               $receiver_hl7v3 Receiver HL7v3
   * @param CXDSQueryRegistryStoredQuery $query          Document
   *
   * @return mixed
   */
  static function sendEventRegistryStoredQuery(CReceiverHL7v3 $receiver_hl7v3, $query) {
    $iti18 = new CHL7v3EventXDSbRegistryStoredQuery();
    $iti18->_event_name = "RegistryStoredQuery";

    $headers = CHL7v3Adressing::createWSAddressing(
      "urn:ihe:iti:2007:RegistryStoredQuery",
      "http://ihexds.nist.gov/tf6/services/xdsregistryb"
    );

    return $receiver_hl7v3->sendEvent($iti18, $query, $headers, true);
  }

  /**
   * Send the event RegistryStoredQuery
   *
   * @param CReceiverHL7v3 $receiver_hl7v3 Receiver HL7v3
   * @param CPatient       $patient        Patient
   * @param String         $repository_id  Repository Id
   * @param String         $oid            OID
   *
   * @return mixed
   */
  static function sendEventRetrieveDocumentSetRequest(CReceiverHL7v3 $receiver_hl7v3, CPatient $patient, $repository_id, $oid) {
    $iti43                = new CHL7v3EventXDSbRetrieveDocumentSet();
    $iti43->oid           = $oid;
    $iti43->repository_id = $repository_id;
    $iti43->type          = CXDSRequest::$type;
    $iti43->_event_name   = "ProvideAndRegisterDocumentSetRequest";

    $headers = CHL7v3Adressing::createWSAddressing(
      "urn:ihe:iti:2007:ProvideAndRegisterDocumentSet-b",
      "http://ihexds.nist.gov/tf6/services/xdsrepositoryb"
    );

    return $receiver_hl7v3->sendEvent($iti43, $patient, $headers, true);
  }

  /**
   * Generate the soap message
   *
   * @param String            $body    body
   * @param CDMPXmlDocument[] $headers headers
   *
   * @return string
   */
  static function generateSOAP($body, $headers) {
    $soap                     = new CMbXMLDocument("UTF-8");
    $soap->formatOutput       = false;
    $soap->preserveWhiteSpace = true;
    $root                     = $soap->addElement($soap, "soap:Envelope", null, "http://schemas.xmlsoap.org/soap/envelope/");
    $node_header              = $soap->addElement($root, "soap:Header", null, "http://schemas.xmlsoap.org/soap/envelope/");
    $node_body                = $soap->addElement($root, "soap:Body", null, "http://schemas.xmlsoap.org/soap/envelope/");

    foreach ($headers as $_header) {
      $soap->importDOMDocument($node_header, $_header);
    }

    $dom_body = new CMbXMLDocument();
    $dom_body->loadXML($body);
    $soap->importDOMDocument($node_body, $dom_body);

    return $soap->saveXML($soap->documentElement);
  }

  /**
   * Get receiver by event
   *
   * @param string $event_name Event name
   * @param int    $group_id   Group id
   *
   * @return CReceiverHL7v3[]
   * @throws Exception
   */
  static function getReceivers($event_name, $group_id = null) {
    $receivers = CReceiverHL7v3::getObjectsByType('none', $group_id);

    foreach ($receivers as $_receiver) {
      $objects = CInteropReceiver::getObjectsBySupportedEvents(array($event_name), $_receiver, true);
      if (!array_key_exists($event_name, $objects)) {
        unset($receivers[$_receiver->_guid]);
      }
    }

    return $receivers;
  }

  /**
   * Get Document Repository receiver
   *
   * @param int $group_id group id
   *
   * @return CReceiverHL7v3[]
   * @throws Exception
   */
  static function getDocumentRepository($group_id = null) {
    return self::getReceivers("CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest", $group_id);
  }

  /**
   * Get Document Registry receiver
   *
   * @return CReceiverHL7v3[]
   * @throws Exception
   */
  static function getDocumentRegistry() {
    return self::getReceivers("CHL7v3EventXDSbRegistryStoredQuery");
  }

  /**
   * Get retrieve document set receiver
   *
   * @return CReceiverHL7v3[]
   * @throws Exception
   */
  static function getRetrieveDocumentSet() {
    return self::getReceivers("CHL7v3EventXDSbRetrieveDocumentSet");
  }
}
