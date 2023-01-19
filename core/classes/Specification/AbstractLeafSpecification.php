<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Abstract class representing specification leaves (not composite ones).
 * Should be used in most cases.
 */
abstract class AbstractLeafSpecification implements SpecificationInterface {
  /**
   * AbstractLeafSpecification constructor.
   */
  final private function __construct() {
  }

  /**
   * Syntactic sugar with Late Static Binding instantiation
   *
   * @param mixed ...$params
   *
   * @return static
   */
  static public function is(...$params): self {
    $spec = new static();

    if ($spec instanceof ParameterizedSpecificationInterface) {
      $spec->setParameters(...$params);
    }

    return $spec;
  }

  /**
   * "Fluent" method for conjunctions.
   *
   * @param SpecificationInterface $specification
   *
   * @return AndX
   */
  public function andX(SpecificationInterface $specification): AndX {
    return new AndX($this, $specification);
  }

  /**
   * "Fluent" method for disjunctions.
   *
   * @param SpecificationInterface $specification
   *
   * @return OrX
   */
  public function orX(SpecificationInterface $specification): OrX {
    return new OrX($this, $specification);
  }

  /**
   * "Fluent" method for exclusive disjunctions.
   *
   * @param SpecificationInterface $specification
   *
   * @return XorX
   */
  public function xorX(SpecificationInterface $specification): XorX {
    return new XorX($this, $specification);
  }

  /**
   * "Fluent" method for negation of current specification.
   *
   * @return Not
   */
  public function not(): Not {
    return new Not($this);
  }

  /**
   * @inheritDoc
   */
  public function remainderUnsatisfiedBy($candidate): ?SpecificationInterface {
    if (!$this->isSatisfiedBy($candidate)) {
      return $this;
    }

    return null;
  }

  /**
   * @param mixed $candidate
   *
   * @return string
   */
  abstract protected function getViolationMessage($candidate): string;

  /**
   * @inheritDoc
   */
  public function toViolation($candidate): SpecificationViolation {
    return SpecificationViolation::create($this, $this->getViolationMessage($candidate));
  }
}