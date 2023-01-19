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
 * Interface CHL7EventMFN
 * Master File Notification
 */
interface CHL7EventMFN {
  /**
   * Construct
   *
   * @return CHL7EventMFN
   */
  function __construct();

  /**
   * Build MFN message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}