<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Mediboard\Mediusers\CMediusers;

class CSyslogQ23Consumer extends CSyslogITI9 {
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

    $this->msg_xml->addAttribute($source_active_participant, 'NetworkAccessPointID', $this->hostname);
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

    $receiver = $this->hl7_exchange->loadRefReceiver()->getFirstExchangesSources();
    $this->msg_xml->addAttribute($destination_active_participant, 'NetworkAccessPointID', $receiver->host);

    $this->msg_xml->addAttribute($destination_active_participant, 'NetworkAccessPointTypeCode', "2");

    $this->destination_active_participant = $destination_active_participant;

    $this->setDestinationActiveParticipantRoleIDCode();
  }

  public function setParticipantObjectIdentification() {
    $this->setQueryParameters();
  }
}
