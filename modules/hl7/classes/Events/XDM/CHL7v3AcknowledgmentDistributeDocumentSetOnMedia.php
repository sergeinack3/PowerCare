<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDM;

use Exception;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * Class CHL7v3AcknowledgmentSVS
 * Acknowledgment SVS
 */
class CHL7v3AcknowledgmentDistributeDocumentSetOnMedia extends CHL7v3AcknowledgmentXDM {
  /**
   * Get query ack
   *
   * @return array
   * @throws Exception
   */
  function getQueryAck() {
  }
}