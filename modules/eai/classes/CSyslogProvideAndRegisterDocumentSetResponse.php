<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Mediboard\Mediusers\CMediusers;

class CSyslogProvideAndRegisterDocumentSetResponse extends CSyslogITI41 {
  public function setSourceActiveParticipant() {
    $source_active_participant = $this->msg_xml->addElement($this->audit_message, 'ActiveParticipant');

    $this->msg_xml->addAttribute(
      $source_active_participant,
      'UserID',
      "{$this->hl7_xml_msh_data['receiving_facility']}|{$this->hl7_xml_msh_data['receiving_application']}"
    );

    $this->msg_xml->addAttribute($source_active_participant, 'AlternativeUserID', CMediusers::get()->_id);
    $this->msg_xml->addAttribute($source_active_participant, 'UserName', trim(CMediusers::get()));
    $this->msg_xml->addAttribute($source_active_participant, 'UserIsRequestor', "true");

    $receiver = $this->hl7_exchange->loadRefReceiver()->getFirstExchangesSources();
    $this->msg_xml->addAttribute($source_active_participant, 'NetworkAccessPointID', $receiver->host);

    $this->msg_xml->addAttribute($source_active_participant, 'NetworkAccessPointTypeCode', "2");

    $this->source_active_participant = $source_active_participant;

    $this->setSourceActiveParticipantRoleIDCode();
  }

  public function setDestinationActiveParticipant() {
    $destination_active_participant = $this->msg_xml->addElement($this->audit_message, 'ActiveParticipant');

    $MSH                   = $this->hl7_xml->queryNode("MSH", null, $foo, true);
    $receiving_facility    = $this->hl7_xml->queryTextNode("MSH.5/HD.1", $MSH);
    $receiving_application = $this->hl7_xml->queryTextNode("MSH.6/HD.1", $MSH);

    $this->msg_xml->addAttribute($destination_active_participant, 'UserID', "{$receiving_facility}|{$receiving_application}");
    $this->msg_xml->addAttribute($destination_active_participant, 'AlternativeUserID', CMediusers::get()->_id);
    $this->msg_xml->addAttribute($destination_active_participant, 'UserName', trim(CMediusers::get()));
    $this->msg_xml->addAttribute($destination_active_participant, 'UserIsRequestor', "false");
    $this->msg_xml->addAttribute($destination_active_participant, 'NetworkAccessPointID', $this->hostname);
    $this->msg_xml->addAttribute($destination_active_participant, 'NetworkAccessPointTypeCode', "2");

    $this->destination_active_participant = $destination_active_participant;

    $this->setDestinationActiveParticipantRoleIDCode();
  }

  public function setParticipantObjectIdentification() {
    $ack = $this->hl7_exchange->getACK();
    $pids = $this->getPIDs($ack);

    foreach ($pids as $_pid) {
      $this->setParticipantObjectIdentificationByPID($_pid);
    }

    $this->setSubmissionSet();
  }

  public function setParticipantObjectIdentificationByPID($pid) {
    $participant_object_identification = $this->msg_xml->addElement($this->audit_message, 'ParticipantObjectIdentification');

    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectTypeCode', '1');
    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectTypeCodeRole', '1');
    $this->msg_xml->addAttribute($participant_object_identification, 'ParticipantObjectID', $pid);

    $this->setParticipantObjectIDTypeCode($participant_object_identification);

    $this->participant_object_identification = $participant_object_identification;
  }
}
