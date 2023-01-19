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
 * "Instance of" specification
 */
class InstanceOfX extends ValueBound {
  use PropertyTrait;

  /**
   * @inheritDoc
   */
  public function isSatisfiedBy($candidate): bool {
    $property_value = $this->getPropertyValue($candidate, $this->property_name);

    return ($property_value instanceof $this->value);
  }

  /**
   * @inheritDoc
   */
  protected function getViolationMessage($candidate): string {
    return sprintf(
      "Object.%s must be an instance of : '%s' (Candidate: '%s').",
      $this->property_name, $this->getFormattedValue($this->value), $this->getFormattedPropertyValue($candidate, $this->property_name)
    );
  }

  /**
   * @inheritDoc
   */
  protected function checkParameters($params) {
    parent::checkParameters($params);

    $value = $params[1];

    if (!is_object($value) && !is_string($value) && !class_exists($value, true)) {
      throw CouldNotCheckSpecification::classNameisInvalid($value);
    }
  }
}