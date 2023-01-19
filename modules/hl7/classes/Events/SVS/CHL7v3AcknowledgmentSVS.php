<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use Ox\Core\CMbXPath;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * Class CHL7v3AcknowledgmentSVS
 * Acknowledgment SVS
 */
class CHL7v3AcknowledgmentSVS extends CHL7v3EventSVS {
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
    /** @var CHL7v3AcknowledgmentSVS $acknowledgment */
    $acknowledgment = new $classname;
    $acknowledgment->dom = $dom;

    return $acknowledgment->getQueryAck();
  }
}