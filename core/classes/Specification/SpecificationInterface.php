<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Description
 */
interface SpecificationInterface {
  /**
   * Tells if an expression is satisfied according to specification.
   *
   * @param mixed $candidate The candidate to specification evaluation.
   *
   * @return bool
   */
  public function isSatisfiedBy($candidate): bool;

  /**
   * Returns the specification that left unsatisfied.
   *
   * @param mixed $candidate The candidate to specification evaluation.
   *
   * @return SpecificationInterface|null
   */
  public function remainderUnsatisfiedBy($candidate): ?SpecificationInterface;

  /**
   * Convert a SpecificationInterface to a SpecificationViolation
   *
   * @param mixed $candidate The specification candidate
   *
   * @return SpecificationViolation
   */
  public function toViolation($candidate): SpecificationViolation;
}