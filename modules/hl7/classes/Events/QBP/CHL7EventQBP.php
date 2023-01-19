<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\QBP;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventQBP
 * Patient Demographics Query
 */
interface CHL7EventQBP {
  /**
   * Construct
   *
   * @return CHL7EventQBP
   */
  function __construct();

  /**
   * Build QBP message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}