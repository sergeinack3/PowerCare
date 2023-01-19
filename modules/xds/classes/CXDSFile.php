<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use DOMElement;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CMbXPath;
use Ox\Interop\Eai\CMbOID;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CXDSFile extends CMbObject {
  /** @var integer Primary key */
  public $xds_file_id;

  public $file_id;
  public $author_id;
  public $patient_id;
  public $version;
  public $create_date;
  public $legal_author;
  public $author;
  public $profession;
  public $title;
  public $description;
  public $event_date_start;
  public $event_date_end;
  public $type_document;
  public $category_xds;
  public $confidentiality;
  public $language;

  public $_visibility;
  public $_status;
  public $_custodian;

  //visibility / status
  public $_old;
  public $_archived;
  public $_new;

  public $_body;

  //filter
  public $_date_max_submit;
  public $_date_min_submit;
  public $_date_max_event;
  public $_date_min_event;
  public $_type_code;

  /** @var  CPatient */
  public $_ref_patient;

  public $returnComposedObjects;
  public $returnType;

  static public $link_xds_query = array(
    "urn:uuid:14d4debf-8f97-4251-9a74-a90016b0af0d" => array(
      "\$XDSDocumentEntryTypeCode",
      "\$XDSDocumentEntryServiceStartTimeFrom",
      "\$XDSDocumentEntryServiceStartTimeTo",
      "\$XDSDocumentEntryConfidentialityCode",
      "\$XDSDocumentEntryStatus",
    ),
    "urn:uuid:f26abbcb-ac74-4422-8a30-edb644bbc1a9" => array(
      "\$XDSSubmissionSetSubmissionTimeFrom",
      "\$XDSSubmissionSetSubmissionTimeTo",
      "\$XDSSubmissionSetStatus",
    ),
  );

  static public $link_xds_prop = array(
    "\$XDSSubmissionSetSubmissionTimeTo"     => "_date_max_submit",
    "\$XDSSubmissionSetSubmissionTimeFrom"   => "_date_min_submit",
    "\$XDSSubmissionSetStatus"               => "_status",
    "\$XDSDocumentEntryServiceStartTimeTo"   => "_date_max_event",
    "\$XDSDocumentEntryServiceStartTimeFrom" => "_date_min_event",
    "\$XDSDocumentEntryTypeCode"             => "_type_doc",
    "\$XDSDocumentEntryConfidentialityCode"  => "_visibility",
    "\$XDSDocumentEntryStatus"               => "_status",
  );

  static public $map_value = array(
    "OLD"      => "urn:oasis:names:tc:ebxml-regrep:StatusType:Deprecated",
    "ARCHIVED" => "urn:asip:ci-sis:2010:StatusType:Archived",
  );

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "xds_file";
    $spec->key    = "xds_file_id";
    return $spec;  
  }
  
  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["file_id"]          = "ref class|CFile back|xds_files";
    $props["author_id"]        = "ref class|CMediusers notNull back|xds_files";
    $props["patient_id"]       = "ref class|CPatient notNull back|xds_files";
    $props["version"]          = "str notNull";
    $props["create_date"]      = "dateTime notNull";
    $props["legal_author"]     = "str";
    $props["author"]           = "str";
    $props["profession"]       = "str";
    $props["title"]            = "str notNull";
    $props["description"]      = "text";
    $props["event_date_start"] = "dateTime";
    $props["event_date_end"]   = "dateTime";
    $props["type_document"]    = "str";
    $props["category_xds"]     = "str";
    $props["confidentiality"]  = "str";
    $props["language"]         = "str";

    $props["_status"]          = "set list|OLD|ARCHIVED";
    $props["_date_max_submit"] = "dateTime";
    $props["_date_min_submit"] = "dateTime";
    $props["_date_max_event"]  = "dateTime";
    $props["_date_min_event"]  = "dateTime";
    
    return $props;
  }

  /**
   * Assigne le patient
   *
   * @param CPatient $patient patient
   *
   * @return void
   */
  function setPatient($patient) {
    $this->patient_id   = $patient->_id;

    $this->_ref_patient = $patient;
  }

  /**
   * Set the value of the filter
   *
   * @param String[]       $values   Value of the GET
   * @param CReceiverHL7v3 $receiver Receiver
   *
   * @return CXDSQueryRegistryStoredQuery
   */
  function getQuery($values, $receiver) {
    $xds_query          = new CXDSQueryRegistryStoredQuery();
    $xds_query->returnComposedObjects = $this->returnComposedObjects;
    $xds_query->returnType            = $this->returnType;
    $xds_query->patient               = $this->_ref_patient;

    $document   = $this->constructArrayQuery($values, "urn:uuid:14d4debf-8f97-4251-9a74-a90016b0af0d");
    $submission = $this->constructArrayQuery($values, "urn:uuid:f26abbcb-ac74-4422-8a30-edb644bbc1a9");

    $xds_query->query = "urn:uuid:14d4debf-8f97-4251-9a74-a90016b0af0d";
    $xds_query->values = $document;
    if (!array_key_exists("\$XDSDocumentEntryStatus", $xds_query->values)) {
      $xds_query->values["\$XDSDocumentEntryStatus"] = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
    }

    if ($submission) {
      $xds_query->query = "urn:uuid:f26abbcb-ac74-4422-8a30-edb644bbc1a9";
      $xds_query->values = $submission;
      if (!array_key_exists("\$XDSSubmissionSetStatus", $xds_query->values)) {
        $xds_query->values["\$XDSSubmissionSetStatus"] = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
      }

      $oid                                              = CMbOID::getOIDOfInstance($this->_ref_patient, $receiver, true);
      $comp4                                            = "&$oid&ISO";
      $comp1                                            = $xds_query->patient->_IPP ? $xds_query->patient->_IPP : $xds_query->patient->_id;
      $xds_query->values["\$XDSSubmissionSetPatientId"] = array(
        "$comp1^^^$comp4"
      );
    }

    return $xds_query;
  }

  /**
   * Construct the values query
   *
   * @param String[] $values Values of the formulaire
   * @param String   $key    Type of query
   *
   * @return array
   */
  function constructArrayQuery($values, $key) {
    $query_values = array();
    foreach (self::$link_xds_query[$key] as $_param) {
      $prop  = CMbArray::get(self::$link_xds_prop, $_param);
      $value = CMbArray::get($values, $prop);
      if (is_array($value)) {
        $value = array_filter($value);
      }
      if (!$value) {
        continue;
      }
      if (is_array($value)) {
        $value = implode("|", $value);
      }
      $value = strtr($value, self::$map_value);
      $query_values[$_param] = array($value);
    }

    return $query_values;
  }

  /**
   * Get the type of the document
   *
   * @return array
   */
  static function getTypeDocument() {
    return CXDSValueSet::load('typeCode', true);
  }

  /**
   * Transform the extrinsic list to DMP File
   *
   * @param DOMElement[] $list_extrinsic Extrinsic object
   *
   * @return CXDSFile[]
   */
  static function transformExtrinsicObject($list_extrinsic) {
    $list_document = array();
    foreach ($list_extrinsic as $_extrinsic) {
      $xpath    = new CMbXPath($_extrinsic->ownerDocument);
      $xpath->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");
      $xds_file = new CXDSFile();
      // @codingStandardsIgnoreStart
      $xds_file->create_date      = $xpath->queryTextNode(".//rim:Slot[@name='creationTime']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->legal_author     = $xpath->queryTextNode(".//rim:Slot[@name='legalAuthenticator']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->author           = $xpath->queryTextNode(".//rim:Classification[@classificationScheme='urn:uuid:93606bcf-9494-43ec-9b4e-a7748d1a838d']/rim:Slot[@name='authorPerson']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->profession       = $xpath->queryTextNode(".//rim:Classification[@classificationScheme='urn:uuid:93606bcf-9494-43ec-9b4e-a7748d1a838d']/rim:Slot[@name='authorSpecialty']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->event_date_start = $xpath->queryTextNode(".//rim:Slot[@name='serviceStartTime']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->event_date_end   = $xpath->queryTextNode(".//rim:Slot[@name='serviceStopTime']/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->title            = $xpath->queryAttributNode("./rim:Name/rim:LocalizedString", $_extrinsic, "value");

      $category_code = $xpath->queryAttributNode(".//rim:Classification[@classificationScheme='urn:uuid:41a5887f-8865-4c09-adf7-e362475b143a']", $_extrinsic, "nodeRepresentation");
      $category_oid  = $xpath->queryTextNode(".//rim:Classification[@classificationScheme='urn:uuid:41a5887f-8865-4c09-adf7-e362475b143a']/rim:Slot/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->category_xds     = "$category_oid^$category_code";

      $type_code = $xpath->queryAttributNode(".//rim:Classification[@classificationScheme='urn:uuid:f0306f51-975f-434e-a61c-c59651d33983']", $_extrinsic, "nodeRepresentation");
      $type_oid  = $xpath->queryTextNode(".//rim:Classification[@classificationScheme='urn:uuid:f0306f51-975f-434e-a61c-c59651d33983']/rim:Slot/rim:ValueList/rim:Value", $_extrinsic);
      $xds_file->type_document    = "$type_oid^$type_code";

      $xds_file->description      = $xpath->queryAttributNode("./rim:Description/rim:LocalizedString", $_extrinsic, "value");
      //$xds_file->version          = $xpath->queryAttributNode(".//rim:VersionInfo", $_extrinsic, "versionName");
      $xds_file->oid              = $xpath->queryAttributNode(".//rim:ExternalIdentifier[@identificationScheme='urn:uuid:2e82c1f6-a085-4c72-9da3-8640a32e42ab']", $_extrinsic, "value");
      $xds_file->repository_id    = $xpath->queryTextNode(".//rim:Slot[@name='repositoryUniqueId']/rim:ValueList/rim:Value", $_extrinsic);

      $visibilite_node = $xpath->query(".//rim:Classification[@classificationScheme='urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f']", $_extrinsic);
      // @codingStandardsIgnoreEnd
      foreach ($visibilite_node as $_visibility) {
        $visiblity_code = $xpath->queryAttributNode(".", $_visibility, "nodeRepresentation");
        if (in_array($visiblity_code, self::$map_value)) {
          $map_xds = array_flip(self::$map_value);
          $field = CMbString::lower($map_xds[$visiblity_code]);
          $xds_file->$field = true;
          break;
        }
      }

      $status = $xpath->queryAttributNode(".", $_extrinsic, "status");
      if ($status == "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved") {
        $xds_file->_status = "APPROVED";
      }
      if ($status == "urn:oasis:names:tc:ebxml-regrep:StatusType:Deprecated") {
        $xds_file->_status = "DEPRECATED";
      }

      $list_document[] = $xds_file;
    }

    return $list_document;
  }
}
