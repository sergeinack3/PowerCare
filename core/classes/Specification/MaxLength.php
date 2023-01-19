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
 * "Max length" specification
 */
class MaxLength extends ValueBound implements SpecificationInterface, ParameterizedSpecificationInterface {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_length = $this->getPropertyLength($candidate, $this->property_name);

    return ($property_length <= $this->value);
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s length must be longer or equal to: '%s' (Candidate: '%s').",
      $this->property_name, $this->getFormattedValue($this->value), $this->getFormattedPropertyLength($candidate, $this->property_name)
    );
  }
}