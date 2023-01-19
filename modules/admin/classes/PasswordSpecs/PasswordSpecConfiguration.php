<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\PasswordSpecs;

use ArrayAccess;
use JsonSerializable;
use ReturnTypeWillChange;

/**
 * Description
 */
class PasswordSpecConfiguration implements ArrayAccess, JsonSerializable
{
    /** @var array */
    private $keys = [];

    /** @var array */
    private $values = [];

    /**
     * Configuration constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $_key => $_value) {
            $this->offsetSet($_key, $_value);
        }
    }


    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return (isset($this->keys[$offset]));
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->values[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->keys[$offset]   = true;
        $this->values[$offset] = $value;
    }


    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->keys[$offset], $this->values[$offset]);
        }
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->values;
    }
}
