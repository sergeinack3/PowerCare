<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2EventADTA49
 * A49 - Change patient account number
 */
class CHL7v2EventADTA49 extends CHL7v2EventADT implements CHL7EventADTA39 {

  /** @var string */
  public $code        = "A49";

  /** @var string */
  public $struct_code = "A39";

  /**
   * Build A49 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($sejour) {
    parent::build($sejour);
  }
}