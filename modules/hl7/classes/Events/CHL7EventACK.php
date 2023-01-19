<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventACK
 * Represents a ACK message structure
 */
interface CHL7EventACK {
  /**
   * Construct
   *
   * @param string|null $i18n i18n
   *
   * @return CHL7EventACK
   */
  function __construct($i18n = null);

  /**
   * Build ACK event
   *
   * @param CMbObject $object Object
   *
   * @return mixed
   */
  function build($object);
}