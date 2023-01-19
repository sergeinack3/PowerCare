<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

/**
 * Filter object used for request API
 */
class Filter
{
    /** @var string */
    private $key;

    /** @var string */
    private $operator;

    /** @var array */
    private $values;

    /**
     * Filter constructor.
     *
     * @param string       $key
     * @param string       $operator
     * @param array|string $values
     */
    public function __construct(string $key, string $operator, $values)
    {
        $this->key      = $key;
        $this->operator = $operator;

        if (!is_array($values)) {
            $values = [$values];
        }

        $this->values = array_filter(
            $values,
            function ($elt) {
                return $elt !== '';
            }
        );
    }

    /**
     * String representation of the Filter.
     * This is used to convert a Filter to a query parameter.
     */
    public function __toString(): string
    {
        return $this->key
            . RequestFilter::FILTER_PART_SEPARATOR
            . $this->operator
            . RequestFilter::FILTER_PART_SEPARATOR
            . implode(RequestFilter::FILTER_PART_SEPARATOR, $this->values);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * @param int $position
     *
     * @return mixed
     */
    public function getValue(int $position = 0)
    {
        return $this->values[$position];
    }
}
