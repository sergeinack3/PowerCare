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
 * Class CHL7v2EventADTA45
 * A45 - Move visit information - visit number
 */
class CHL7v2EventADTA45 extends CHL7v2EventADT implements CHL7EventADTA45 {
  /** @var string */
  public $code        = "A45";

  /** @var string */
  public $struct_code = "A45";

  /**
   * Build A45 event
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