<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventSIU
 * Scheduled Workflow
 */
interface CHL7EventSIU {
  /**
   * Construct
   *
   * @return CHL7EventSIU
   */
  function __construct();

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object);
}