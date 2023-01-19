<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDM;

use Ox\Core\CMbXPath;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * Class CHL7v3AcknowledgmentXDM
 * Acknowledgment XDM
 */
class CHL7v3AcknowledgmentXDM extends CHL7v3EventXDM {
  /** @var CHL7v3MessageXML */
  public $dom;
  public $status;

  /** @var CMbXPath */
  public $xpath;

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    return "OK";
  }

  /**
   * Get query ack
   *
   * @return array
   */
  function getQueryAck() {
    $dom   = $this->dom;

    $classname = "CHL7v3Acknowledgment".$dom->documentElement->localName;
    /** @var CHL7v3AcknowledgmentXDM $acknowledgment */
    $acknowledgment = new $classname;
    $acknowledgment->dom = $dom;

    return $acknowledgment->getQueryAck();
  }
}