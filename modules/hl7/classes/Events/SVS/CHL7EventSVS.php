<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use Ox\Core\CMbObject;

/**
 * Event SVS - Sharing Value Sets
 */
interface CHL7EventSVS {
  /**
   * Construct
   *
   * @return CHL7EventSVS
   */
  function __construct();

  /**
   * Build SVS message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}
