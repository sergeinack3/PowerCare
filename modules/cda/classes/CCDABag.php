<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Symfony\Component\HttpFoundation\ParameterBag;

class CCDABag
{
    /** @var ParameterBag */
    private $items;

    public function __construct(array $parameters = [])
    {
        $this->items = new ParameterBag($parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->items->get($key, $default);
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set(string $key, $value)
    {
        $this->items->set($key, $value);
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(): array
    {
        return $this->items->all();
    }

    /**
     * Return new object with parameters and bag merged.
     * Parameters arguments replaced elements with same key in bag arguments
     *
     * @param array        $parameters
     * @param CCDABag|null $copy
     *
     * @return static
     */
    public static function merge(array $parameters, self $copy = null): self
    {
        if ($copy) {
            $parameters = array_replace($copy->all(), $parameters);
        }

        return new CCDABag($parameters);
    }
}
