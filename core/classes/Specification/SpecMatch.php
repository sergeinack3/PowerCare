<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

use Ox\Core\Specification\Exception\CouldNotCheckSpecification;
use Ox\Core\Specification\Traits\PropertyTrait;

/**
 * "Match" specification
 */
class SpecMatch extends ValueBound {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_value = $this->getPropertyValue($candidate, $this->property_name);

    if (!is_null($property_value) && !is_scalar($property_value)) {
      throw CouldNotCheckSpecification::matchError($this->getFormattedValue($property_value));
    }

    $matches = preg_match($this->value, $property_value ?? "");

    if ($matches === false) {
      throw CouldNotCheckSpecification::matchError($this->getFormattedValue($property_value));
    }

    return ($matches === 1);
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s must match the regex : '%s' (Candidate: '%s').",
      $this->property_name, $this->getFormattedValue($this->value), $this->getFormattedPropertyValue($candidate, $this->property_name)
    );
  }

  /**
   * @inheritDoc
   */
  protected function checkParameters($params) {
    parent::checkParameters($params);

    if (!is_string($params[1])) {
      throw CouldNotCheckSpecification::invalidPattern();
    }
  }
}
