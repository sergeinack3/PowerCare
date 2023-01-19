<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification\Exception;

use Ox\Core\CMbException;

/**
 * Description
 */
class CouldNotGetPropertyLength extends CMbException {
  /**
   * @param mixed $property_value
   *
   * @return self
   */
  public static function isNotCountableOrString($property_value): self {
    return new self('CouldNotGetPropertyLenth-error-%s is not a countable or a string', $property_value);
  }
}
