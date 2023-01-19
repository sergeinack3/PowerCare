<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MFN;

use Ox\Core\CMbObject;

/**
 * Class CHL7v2EventMFNM15
 * Inventory Item Master File Notification - HL7
 */
class CHL7v2EventMFNM15 extends CHL7v2EventMFN implements CHL7EventMFNM15 {

  /** @var string */
  public $code = "M15";

  /** @var string */
  public $struct_code = "M15";

  /**
   * Build M15 event
   *
   * @param CMbObject $object object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);
  }
}