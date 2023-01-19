<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Ox\Core\CMbObject;

/**
 * Event XDSb
 */
interface CHL7EventXDSb {
  /**
   * Construct
   *
   * @return CHL7EventXDSb
   */
  function __construct();

  /**
   * Build XDSb message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}
