<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use Exception;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * Class CHL7v3AcknowledgmentSVS
 * Acknowledgment SVS
 */
class CHL7v3AcknowledgmentRetrieveValueSetResponse extends CHL7v3AcknowledgmentSVS {
  /**
   * Get query ack
   *
   * @return array
   * @throws Exception
   */
  function getQueryAck() {
    /** @var CHL7v3MessageXML $dom */
    $dom = $this->dom;

    $prefix = "svs:";
    $_value_set = $dom->queryNode("{$prefix}ValueSet");

    $value_set = new CHL7v3EventSVSValueSet($dom);
    $value_set->bind($dom, $_value_set, $prefix);

    return $value_set;
  }
}