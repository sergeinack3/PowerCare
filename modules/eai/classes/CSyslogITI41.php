<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use DOMElement;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Field;
use Ox\Interop\Hl7\CHL7v2FieldItem;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;

/**
 * ITI-41 Patient Demographics Consumer audit message
 *
 * Patient Demographics and Visit Query
 *
 * Look up and return patient demographic and visit information in a single patient demographics source,
 * based upon matches with full or partial demographic/visit information entered by the user.
 */
class CSyslogITI41 extends CSyslogAuditMessage {
  const AUDIT_SOURCE_ID = 'MEDIBOARD';

  /** @var CHL7v2Message */
  public $hl7_msg;

  /** @var CHl7v2MessageXML */
  public $hl7_xml;

  /** @var array */
  public $hl7_xml_msh_data;

  /** @var CExchangeHL7v2 */
  public $hl7_exchange;

  /** @var CMbXMLDocument */
  public $msg_xml;

  /** @var DOMElement */
  public $audit_message;

  /** @var DOMElement */
  public $event_identification;

  /** @var DOMElement */
  public $event_identification_event_id;

  /** @var DOMElement */
  public $event_identification_event_type_code;

  /** @var DOMElement */
  public $source_active_participant;

  /** @var DOMElement */
  public $source_active_participant_role_id_code;

  /** @var DOMElement */
  public $destination_active_participant;

  /** @var DOMElement */
  public $destination_active_participant_role_id_code;

  /** @var DOMElement */
  public $audit_source_identification;

  /** @var DOMElement */
  public $participant_object_identification;

  /** @var DOMElement */
  public $submission_set;

  /** @var DOMElement */
  public $submission_set_participant_object_id_type_code;

  public function setExchange(CExchangeHL7v2 $exchange) {
    $msg = $exchange->getMessage();

    $this->hl7_msg          = $msg;
    $this->hl7_xml          = $msg->toXML();
    $this->hl7_xml_msh_data = $this->hl7_xml->getMSHEvenementXML();
    $this->hl7_exchange     = $exchange;
    $this->msg_xml          = new CMbXMLDocument('utf-8');

    $this->setMsg($msg);
  }

  static function getInstance(CExchangeHL7v2 $exchange) {
    $msg = $exchange->getMessage();

    if (self::isSource($msg->toXML()->getMSHEvenementXML())) {
      $object = new CSyslogProvideAndRegisterDocumentSetResponse();
    }
    else {
      // Not implemented yet
      return false;
    }

    $object->setExchange($exchange);

    return $object;
  }

  static function isSource($msh_data) {
    $sending_facility    = CAppUI::conf('hl7 CHL7 sending_facility'   , "global");
    $sending_application = CAppUI::conf('hl7 CHL7 sending_application', "global");

    return ($msh_data['receiving_facility'] == $sending_facility
      && $msh_data['receiving_application'] == $sending_application);
  }

  /**
   * @param CHL7v2Message $msg
   */
  public function setMsg(CHL7v2Message $msg) {
    parent::setMsg($this->setAuditMessage());
  }

  /**
   * Constructs XML audit message
   *
   * @return string    XML audit message
   * @throws Exception
   */
  public function setAuditMessage() {
    $this->audit_message = $this->msg_xml->addElement($this->msg_xml, 'AuditMessage');

    // Event
    $this->setEventIdentification();

    // Source
    $this->setSourceActiveParticipant();

    // Destination
    $this->setDestinationActiveParticipant();

    // Audit Source
    $this->setAuditSourceIdentification();

    // Patient
    $this->setParticipantObjectIdentification();

    return $this->msg_xml->saveXML();
  }

  public function setEventIdentification() {
    $event_identification = $this->msg_xml->addElement($this->audit_message, 'EventIdentification');

    $this->msg_xml->addAttribute($event_identification, 'EventActionCode', 'C');
    $this->msg_xml->addAttribute(
      $event_identification,
      'EventDateTime',
      CMbDT::date(null, $this->hl7_xml_msh_data['dateHeureProduction'])
      . 'T' . CMbDT::time(null, $this->hl7_xml_msh_data['dateHeureProduction']) . 'Z'
    );

    $this->event_identification = $event_identification;

    $this->setEventOutcomeIndicatorAttribute();

    $this->setEventID();
    $this->setEventTypeCode();
  }

  /**
   * 0  - Nominal Success (use if status otherwise unknown or ambiguous)
   * 4  - Minor failure (per reporting application definition)
   * 8  - Serious failure (per reporting application definition)
   * 12 - Major failure, (reporting application now unavailable)
   */
  public function setEventOutcomeIndicatorAttribute() {
    $this->msg_xml->addAttribute($this->event_identification, 'EventOutcomeIndicator', '0');
  }

  public function setEventID() {
    $event_identification_event_id = $this->msg_xml->addElement($this->event_identification, 'EventID');

    $this->setEVAttributes($event_identification_event_id, '110107', 'Import', 'DCM');

    $this->event_identification_event_id = $event_identification_event_id;
  }

  public function setEventTypeCode() {
    $event_identification_event_type_code = $this->msg_xml->addElement($this->event_identification, 'EventTypeCode');

    $this->setEVAttributes($event_identification_event_type_code, 'ITI-41', 'Provide and Register Document Set-b', 'IHE Transactions');

    $this->event_identification_event_type_code = $event_identification_event_type_code;
  }

  public function setSourceActiveParticipant() {
    throw new Exception(__METHOD__ . " must be redefined");
  }

  public function setSourceActiveParticipantRoleIDCode() {
    $source_active_participant_role_id_code = $this->msg_xml->addElement($this->source_active_participant, 'RoleIDCode');

    $this->setEVAttributes($source_active_participant_role_id_code, '110153', 'Source', 'DCM');

    $this->source_active_participant_role_id_code = $source_active_participant_role_id_code;
  }

  public function setDestinationActiveParticipant() {
    throw new Exception(__METHOD__ . " must be redefined");
  }

  public function setDestinationActiveParticipantRoleIDCode() {
    $destination_active_participant_role_id_code = $this->msg_xml->addElement($this->destination_active_participant, 'RoleIDCode');

    $this->setEVAttributes($destination_active_participant_role_id_code, '110152', 'Destination', 'DCM');

    $this->destination_active_participant_role_id_code = $destination_active_participant_role_id_code;
  }

  public function setAuditSourceIdentification() {
    $audit_source_identification = $this->msg_xml->addElement($this->audit_message, 'AuditSourceIdentification');

    $this->msg_xml->addAttribute($audit_source_identification, 'AuditSourceID', self::AUDIT_SOURCE_ID);

    $this->audit_source_identification = $audit_source_identification;

    $this->setAuditSourceIdentificationTypeCodeAttribute();
  }

  /**
   * 1 - End-user display device, diagnostic display
   * 2 - Data acquisition device or instrument
   * 3 - Web server process
   * 4 - Application server process
   * 5 - Database server process
   * 6 - Security server, e.g., a domain controller
   * 7 - ISO level 1-3 network component
   * 8 - ISO level 4-6 operating software
   * 9 - External source, other or unknown type
   */
  public function setAuditSourceIdentificationTypeCodeAttribute() {
    $this->msg_xml->addAttribute($this->audit_source_identification, 'code', '4');
  }

  public function setParticipantObjectIdentification() {
    throw new Exception(__METHOD__ . " must be redefined");
  }

  public function setParticipantObjectIDTypeCode(DOMElement $participant_object_identification) {
    $participant_object_id_type_code = $this->msg_xml->addElement($participant_object_identification, 'ParticipantObjectIDTypeCode');

    $this->setEVAttributes($participant_object_id_type_code, '2', 'Patient Number', 'RFC-3881');
  }

  public function setSubmissionSet() {
    $participant_object_identification = $this->msg_xml->addElement($this->audit_message, 'ParticipantObjectIdentification');

    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectID', '');
    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectTypeCode', '2');
    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectTypeCodeRole', '20');

    $this->submission_set = $participant_object_identification;

    $this->setSubmissionSetParticipantObjectIDTypeCode();
  }

  public function setSubmissionSetParticipantObjectIDTypeCode() {
    $participant_object_id_type_code = $this->msg_xml->addElement($this->submission_set, 'ParticipantObjectIDTypeCode');

    $this->setEVAttributes(
      $participant_object_id_type_code,
      'urn:uuid:a54d6aa5-d40d-43f9-88c5-b4633d873bdd', 'submission set classificationNode', 'IHE XDS Metadata');

    $this->submission_set_participant_object_id_type_code = $participant_object_id_type_code;
  }

  public function setEVAttributes(DOMElement $dom_node, $csd_code, $original_text, $code_system_name) {
    $this->msg_xml->addAttribute($dom_node, 'csd-code', $csd_code);
    $this->msg_xml->addAttribute($dom_node, 'originalText', $original_text);
    $this->msg_xml->addAttribute($dom_node, 'codeSystemName', $code_system_name);
  }

  /**
   * Gets PIDs from HL7v2 fields
   *
   * @param $msg
   *
   * @return array
   */
  public function getPIDs($msg) {
    $query_responses = array();
    foreach ($msg->children as $_segment) {
      if ($_segment->name == 'QUERY_RESPONSE') {
        $query_responses[] = $_segment;
      }
    }

    $pids = array();
    if ($query_responses) {
      foreach ($query_responses as $_query_response) {
        foreach ($_query_response->children as $_segment) {
          /** @var CHL7v2Field $field */
          $field = $_segment->fields[2];

          /** @var CHL7v2FieldItem $_item */
          foreach ($field->items as $_item) {
            if ($pos = strrpos($_item->data, '&ISO')) {
              $data   = substr($_item->data, 0, $pos + 4);
              $pids[] = $data;
            }
          }
        }
      }
    }

    return $pids;
  }
}
