<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\QBP;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\Events\CHL7Event;

/**
 * Interface CHL7EventRSP
 * Represents a RSP message structure
 */
interface CHL7EventRSP {
  /**
   * Construct
   *
   * @param CHL7Event $trigger_event Trigger event
   *
   * @return CHL7EventRSP
   */
  function __construct(CHL7Event $trigger_event);

  /**
   * Build QBP message
   *
   * @param CMbObject $object object
   *
   * @return mixed
   */
  function build($object);
}