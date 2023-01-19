<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Conjunction specification
 */
final class AndX extends AbstractCompositeSpecification implements SpecificationInterface
{
    /**
     * If at least one specification is false, return false, else return true.
     *
     * @param mixed $candidate Candidate to specification evaluation
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool
    {
        foreach ($this->specifications as $_specification) {
            if (!$_specification->isSatisfiedBy($candidate)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getToken(): string
    {
        return 'and';
    }
}
