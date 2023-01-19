<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Disjunction specification
 */
final class OrX extends AbstractCompositeSpecification implements SpecificationInterface
{
    /**
     * If at least one specification is true, return true, else return false.
     *
     * @param mixed $candidate Candidate to specification evaluation
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool
    {
        foreach ($this->specifications as $_specification) {
            if ($_specification->isSatisfiedBy($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getToken(): string
    {
        return 'or';
    }
}
