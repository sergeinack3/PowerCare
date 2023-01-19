<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Exclusive disjunction specification
 */
final class XorX extends AbstractCompositeSpecification implements SpecificationInterface
{
    /**
     * If the specifications return distinct values, return true, else return false.
     *
     * @param mixed $candidate Candidate to specification evaluation
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool
    {
        $xor = 0;

        foreach ($this->specifications as $_specification) {
            $xor ^= $_specification->isSatisfiedBy($candidate);
        }

        return (bool)$xor;
    }

    /**
     * @inheritDoc
     */
    public static function getToken(): string
    {
        return 'xor';
    }

    /**
     * @inheritDoc
     */
    public function remainderUnsatisfiedBy($candidate): ?SpecificationInterface
    {
        // If composition is satisfied, all is OK
        if ($this->isSatisfiedBy($candidate)) {
            return null;
        }

        // Get the specifications which are NOT satisfied
        /** @var SpecificationInterface[] $specifications */
        $specifications = array_filter(
            $this->specifications,
            function (SpecificationInterface $specification) use ($candidate) {
                return !($specification->isSatisfiedBy($candidate));
            }
        );

        // XOR special case: all specifications are individually satisfied
        if (!$specifications) {
            return new static(...$this->specifications);
        }

        // If only one...
        if (count($specifications) === 1) {
            $specification = reset($specifications);

            // If if is a composite, we do a recursive call, composite by composite, in order to descend to the leaves
            if ($specification instanceof self) {
                return $specification->remainderUnsatisfiedBy($candidate);
            }

            // Else, we return the leaf
            return $specification;
        }
        // Removing by reference (replacing by null) satisfied leaf specifications inside composite ones
        foreach ($specifications as &$_sub_spec) {
            $_sub_spec = $_sub_spec->remainderUnsatisfiedBy($candidate);
        }

        // We return the composite specification which is not satisfied
        return new static(...$specifications);
    }
}
