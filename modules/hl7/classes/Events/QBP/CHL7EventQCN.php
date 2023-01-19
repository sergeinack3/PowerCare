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
 * Interface CHL7EventQCN
 * Patient Demographics Query Cancel Query
 */
interface CHL7EventQCN {
  /**
   * Construct
   *
   * @return CHL7EventQCN
   */
  function __construct();

  /**
   * Build QCN message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}