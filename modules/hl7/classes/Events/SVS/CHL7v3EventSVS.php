<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v3MessageXML;
use Ox\Interop\Hl7\Events\CHL7v3Event;

/**
 * Event SVS - Sharing Value Sets
 */
class CHL7v3EventSVS extends CHL7v3Event implements CHL7EventSVS {
  /**
   * Construct
   *
   * @return CHL7v3EventSVS
   */
  function __construct() {
    parent::__construct();

    $this->event_type = "SVS";
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    $this->dom = new CHL7v3MessageXML("utf-8", $this->version);

    parent::build($object);
  }
}
