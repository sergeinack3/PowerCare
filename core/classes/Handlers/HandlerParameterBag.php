<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Description
 */
class HandlerParameterBag implements IteratorAggregate, Countable
{
    /** @var array */
    private $handlers = [];


    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->handlers);
    }


    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->handlers);
    }

    /**
     * @param string      $handler_class
     * @param bool        $default_value
     * @param string|null $additional_spec
     *
     * @return $this
     */
    public function register(string $handler_class, bool $default_value, ?string $additional_spec = null): self
    {
        $this->handlers[$handler_class] = [
            'default' => $default_value,
            'extra'   => $additional_spec,
        ];

        return $this;
    }
}
