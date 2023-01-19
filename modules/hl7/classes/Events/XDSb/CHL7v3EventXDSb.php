<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\Events\CHL7v3Event;

/**
 * Event XDSb
 */
class CHL7v3EventXDSb extends CHL7v3Event implements CHL7EventXDSb {
  /**
   * Construct
   *
   * @return CHL7v3EventXDSb
   */
  function __construct() {
    parent::__construct();

    $this->event_type = "XDSb";
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
    parent::build($object);
  }
}
