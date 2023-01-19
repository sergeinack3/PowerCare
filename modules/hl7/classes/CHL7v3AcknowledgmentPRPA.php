<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;



use Ox\Interop\Hl7\Events\PRPA\CHL7v3EventPRPA;

/**
 * Class CHL7v3AcknowledgmentPRPA
 * Acknowledgment HL7v3
 */
class CHL7v3AcknowledgmentPRPA extends CHL7v3EventPRPA {
  public $acknowledgment;

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
  }

  /**
   * Get acknowledgment text
   *
   * @return string
   */
  function getTextAcknowledgment() {
    $dom = $this->dom;
    $acknowledgementDetail = $dom->queryNode("//hl7:acknowledgementDetail", $this->acknowledgment->parentNode);

    return $dom->queryTextNode("hl7:text", $acknowledgementDetail);
  }

  /**
   * Get reason code of ack
   *
   * @return string
   */
  function getReasonCode() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $reasonCode = $dom->queryNode("$interaction_id/hl7:controlActProcess/hl7:reasonCode");

    return $dom->getValueAttributNode($reasonCode, "code");
  }

  /**
   * Get query ack
   *
   * @return string
   */
  function getQueryAck() {
  }
}
