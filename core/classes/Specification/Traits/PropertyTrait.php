<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification\Traits;

use Ox\Core\Specification\Exception\CouldNotGetPropertyLength;
use Ox\Core\Specification\Exception\CouldNotGetPropertyValue;
use ReflectionClass;
use ReflectionException;

trait PropertyTrait {
  /**
   * Get the value a candidate property (potentially private property)
   *
   * @param mixed  $candidate
   * @param string $name
   *
   * @return mixed
   * @throws CouldNotGetPropertyValue
   */
  protected function getPropertyValue($candidate, string $name) {
    if (!is_object($candidate)) {
      throw CouldNotGetPropertyValue::isNotAnObject();
    }
    try {
      $reflexion = new ReflectionClass($candidate);
      $property  = $reflexion->getProperty($name);

      if (!$property->isPublic()) {
        $property->setAccessible(true);
      }

      return $property->getValue($candidate);
    }
    catch (ReflectionException $e) {
      throw CouldNotGetPropertyValue::classNotFoundForReflexion($candidate, $e);
    }
  }

  /**
   * @param mixed  $candidate
   * @param string $name
   *
   * @return mixed
   */
  protected function getFormattedPropertyValue($candidate, string $name) {
    try {
      $value     = $this->getPropertyValue($candidate, $name);
      $formatted = $this->getFormattedValue($value);
    }
    catch (CouldNotGetPropertyValue $e) {
      $formatted = 'CANNOT FORMAT';
    }

    return $formatted;
  }

  /**
   * @param mixed $value
   *
   * @return bool|float|int|string
   */
  protected function getFormattedValue($value) {
    if (is_null($value)) {
      return 'NULL';
    }

    if (!is_scalar($value)) {
      return 'CANNOT FORMAT';
    }

    return $value;
  }

  /**
   * @param mixed  $candidate
   * @param string $name
   *
   * @return int
   * @throws CouldNotGetPropertyLength
   * @throws CouldNotGetPropertyValue
   */
  protected function getPropertyLength($candidate, string $name): int {
    /** @var mixed $property_value */
    $property_value = $this->getPropertyValue($candidate, $name);

    if (!is_countable($property_value) && !is_string($property_value) && !is_null($property_value)) {
      throw CouldNotGetPropertyLength::isNotCountableOrString($property_value);
    }

    if ($property_value === null) {
        return 0;
    }
    
    // mb_strlen(NULL) = 0
    return (is_countable($property_value)) ? count($property_value) : mb_strlen($property_value, 'ISO-8859-1');
  }

  /**
   * @param mixed  $candidate
   * @param string $name
   *
   * @return mixed
   */
  protected function getFormattedPropertyLength($candidate, string $name) {
    try {
      $value     = $this->getPropertyLength($candidate, $name);
      $formatted = $this->getFormattedValue($value);
    }
    catch (CouldNotGetPropertyValue $e) {
      $formatted = 'CANNOT FORMAT';
    }
    catch (CouldNotGetPropertyLength $e) {
      $formatted = 'CANNOT FORMAT';
    }

    return $formatted;
  }
}
