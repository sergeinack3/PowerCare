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
 * Null specification
 */
class IsNull extends ValueBound implements SpecificationInterface, ParameterizedSpecificationInterface {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function setParameters(...$params): ParameterizedSpecificationInterface {
    $this->checkParameters($params);

    list($property_name,) = $params;

    $this->property_name = $property_name;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_value = $this->getPropertyValue($candidate, $this->property_name);

    return ($property_value === null);
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s must be null (Candidate: '%s').",
      $this->property_name, $this->getFormattedPropertyValue($candidate, $this->property_name)
    );
  }

  /**
   * @inheritDoc
   */
  protected function checkParameters($params) {
    if (!$params || !is_array($params)) {
      throw CouldNotCreateSpecification::invalidParameters();
    }

    if (count($params) < 1) {
      throw CouldNotCreateSpecification::missingParameters();
    }

    if (!is_string($params[0])) {
      throw CouldNotCreateSpecification::noPropertyParameter();
    }
  }
}