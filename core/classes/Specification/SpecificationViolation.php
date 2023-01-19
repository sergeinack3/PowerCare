<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

class SpecificationViolation
{
    /** @var string */
    private $type;

    /** @var array */
    private $list = [];

    /**
     * SpecificationViolation constructor.
     *
     * @param SpecificationInterface $specification
     */
    public function __construct(SpecificationInterface $specification)
    {
        $this->type = get_class($specification);
    }

    /**
     * @param SpecificationInterface       $specification
     * @param mixed|SpecificationInterface $violation
     *
     * @return self
     */
    public static function create(SpecificationInterface $specification, $violation): self
    {
        $self = new self($specification);
        $self->add($violation);

        return $self;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed|SpecificationViolation $violation
     *
     * @return void
     */
    public function add($violation): void
    {
        $this->list[] = $violation;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $violations = [];

        $glue = false;
        if (is_a($this->type, AbstractCompositeSpecification::class, true)) {
            $token = call_user_func([$this->type, 'getToken']);
            $glue  = strtoupper(" {$token} ");
        }

        foreach ($this->list as $_violation) {
            // Todo: Check with Stringable interface when available
            $violations[] = (string)$_violation;
        }

        // When no glue, $violations is an array with one scalar value
        return ($glue) ? '(' . implode($glue, $violations) . ')' : implode(' ', $violations);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $violations = [];

        $index = false;
        if (is_a($this->type, AbstractCompositeSpecification::class, true)) {
            $token = call_user_func([$this->type, 'getToken']);
            $index = strtoupper($token);
        }

        // No index, so Leaf Specification, just return the violation
        if (!$index) {
            // Todo: Actually not quite consistent with __toString, ie when adding two violations without a Composite type
            $violations[] = reset($this->list);

            return $violations;
        }

        $_violations = [];
        foreach ($this->list as $_violation) {
            $_violations[] = $_violation->toArray();
        }

        // Put the violations in composite-indexed array
        $violations[$index] = $_violations;

        return $violations;
    }
}
