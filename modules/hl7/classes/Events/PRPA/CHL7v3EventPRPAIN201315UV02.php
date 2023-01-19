<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Interop\Hl7\CHL7v3AcknowledgmentPRPA;

/**
 * Class CHL7v3EventPRPAIN201315UV02
 * Patient Registry Add Request Accepted
 */
class CHL7v3EventPRPAIN201315UV02 extends CHL7v3AcknowledgmentPRPA implements CHL7EventPRPAST201317UV02 {
  /** @var string */
  public $interaction_id = "IN201315UV02";
  public $queryAck;
  public $subject;

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
    return "{$this->event_type}_{$this->interaction_id}";
  }

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    $dom = $this->dom;

    $this->acknowledgment = $dom->queryNode("//hl7:".$this->getInteractionID()."/hl7:acknowledgement");

    return $dom->getValueAttributNode($this->acknowledgment, "typeCode");
  }

  /**
   * Get query ack
   *
   * @return string
   */
  function getQueryAck() {
  }
}