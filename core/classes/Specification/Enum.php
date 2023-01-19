<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

use Ox\Core\Specification\Exception\CouldNotCreateSpecification;
use Ox\Core\Specification\Traits\PropertyTrait;

/**
 * Enum specification
 */
class Enum extends ValueBound implements SpecificationInterface, ParameterizedSpecificationInterface {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_value = $this->getPropertyValue($candidate, $this->property_name);

    return (in_array($property_value, $this->value));
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s must one of theses values: '%s' (Candidate: '%s').",
      $this->property_name, implode(', ', $this->value), $this->getFormattedPropertyValue($candidate, $this->property_name)
    );
  }

  /**
   * @inheritDoc
   */
  protected function checkParameters($params) {
    parent::checkParameters($params);

    if (!is_array($params[1]) || empty($params[1])) {
      throw CouldNotCreateSpecification::invalidParameters();
    }
  }
}