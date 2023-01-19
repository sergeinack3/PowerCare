<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Abstraction of a composite specification (conjunction, disjunction and exclusive disjunction)
 * A composite is used as it treats a group of specifications (the leaves) as a single one (the component)
 */
abstract class AbstractCompositeSpecification implements SpecificationInterface
{
    /** @var SpecificationInterface[] */
    protected $specifications;

    /**
     * AbstractCompositeSpecification constructor.
     *
     * @param SpecificationInterface ...$specifications
     */
    final public function __construct(SpecificationInterface ...$specifications)
    {
        $this->specifications = $specifications;
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

        // None
        if (!$specifications) {
            return null;
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

    /**
     * @inheritDoc
     */
    public function toViolation($candidate): SpecificationViolation
    {
        $violation = new SpecificationViolation($this);

        foreach ($this->specifications as $_specification) {
            $violation->add($_specification->toViolation($candidate));
        }

        return $violation;
    }

    /**
     * Get the token of the logical operation
     *
     * @return string
     */
    abstract public static function getToken(): string;
}
