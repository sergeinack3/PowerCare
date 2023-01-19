<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventPRPA
 * Patient Registry
 */
interface CHL7EventPRPA {
  /**
   * Construct
   *
   * @return CHL7EventPRPA
   */
  function __construct();

  /**
   * Build PRPA message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}