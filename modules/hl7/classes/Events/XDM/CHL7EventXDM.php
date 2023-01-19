<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDM;

use Ox\Core\CMbObject;

/**
 * Event XDM - Cross-Enterprise Document Media Interchange
 */
interface CHL7EventXDM {
  /**
   * Construct
   *
   * @return CHL7EventXDM
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
