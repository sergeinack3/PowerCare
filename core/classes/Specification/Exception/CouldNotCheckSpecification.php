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
class CouldNotCheckSpecification extends CMbException {
  /**
   * @param mixed $property_value
   *
   * @return self
   */
  public static function matchError($property_value): self {
    return new self('CouldNotCheckSpecification-error-Error during matching %s', $property_value);
  }

  /**
   * @param mixed $property_value
   *
   * @return self
   */
  public static function classNameIsInvalid($property_value): self {
    return new self('CouldNotCheckSpecification-error-Class name is not a valid object or a string %s', $property_value);
  }

  /**
   * @return self
   */
  public static function invalidPattern(): self {
    return new self('CouldNotCheckSpecification-error-Match pattern must be a string');
  }
}
