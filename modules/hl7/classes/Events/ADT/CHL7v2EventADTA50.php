<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA50
 * A50 - Change visit number
 */
class CHL7v2EventADTA50 extends CHL7v2EventADT implements CHL7EventADTA50 {

  /** @var string */
  public $code        = "A50";

  /** @var string */
  public $struct_code = "A50";

  /**
   * Build A50 event
   *
   * @param CSejour $sejour Admit
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($sejour) {
  }
}