<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

use Ox\Core\Specification\Traits\PropertyTrait;

/**
 * "Less than or equal" specification
 */
class LessThanOrEqual extends ValueBound implements SpecificationInterface, ParameterizedSpecificationInterface {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_value = $this->getPropertyValue($candidate, $this->property_name);

    if ($property_value === null) {
      return false;
    }

    return ($property_value <= $this->value);
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s must be less than or equal to: '%s' (Candidate: '%s').",
      $this->property_name, $this->getFormattedValue($this->value), $this->getFormattedPropertyValue($candidate, $this->property_name)
    );
  }
}