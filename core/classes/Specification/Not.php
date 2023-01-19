<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Negation specification
 */
final class Not implements SpecificationInterface {
  /** @var SpecificationInterface */
  private $specification;

  /**
   * Not constructor.
   *
   * @param SpecificationInterface $specification
   */
  public function __construct(SpecificationInterface $specification) {
    $this->specification = $specification;
  }

  /**
   * If the specification is false, return true, else return false.
   *
   * @param mixed $candidate Candidate to specification evaluation
   *
   * @return bool
   */
  public function isSatisfiedBy($candidate): bool {
    return !($this->specification->isSatisfiedBy($candidate));
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
   * Todo: Message will be ambiguous cause of absence of negation
   *
   * @inheritDoc
   */
  public function toViolation($candidate): SpecificationViolation {
    return SpecificationViolation::create($this, $this->specification->toViolation($candidate));
  }
}