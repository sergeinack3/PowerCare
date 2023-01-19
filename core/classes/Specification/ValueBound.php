<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

use Ox\Core\Specification\Exception\CouldNotCreateSpecification;

/**
 * Abstract class representing specification with bounded values
 */
abstract class ValueBound extends AbstractLeafSpecification implements SpecificationInterface, ParameterizedSpecificationInterface {
  /** @var string */
  protected $property_name;

  /** @var mixed */
  protected $value;

  /**
   * @inheritDoc
   */
  public function setParameters(...$params): ParameterizedSpecificationInterface {
    $this->checkParameters($params);

    list($property_name, $value,) = $params;

    $this->property_name = $property_name;
    $this->value         = $value;

    return $this;
  }

  /**
   * @param mixed|null $params
   *
   * @return void
   * @throws CouldNotCreateSpecification
   */
  protected function checkParameters($params) {
    if (!$params || !is_array($params)) {
      throw CouldNotCreateSpecification::invalidParameters();
    }

    if (count($params) < 2) {
      throw CouldNotCreateSpecification::missingParameters();
    }

    if (!is_string($params[0])) {
      throw CouldNotCreateSpecification::noPropertyParameter();
    }
  }
}