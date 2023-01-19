<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Ox\Core\Security\Csrf\Exceptions\CouldNotGetCsrfToken;
use Ox\Core\Security\Csrf\Exceptions\CouldNotUseCsrf;
use ReturnTypeWillChange;
use Traversable;

/**
 * Description
 */
class AntiCsrfTokenParameterBag implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var AntiCsrf */
    private $anti_csrf;

    /** @var array */
    protected $parameters;

    /**
     * AntiCsrfTokenParameterBag constructor.
     *
     * @param AntiCsrf $anti_csrf
     * @param string   $name
     * @param array    $parameters
     */
    public function __construct(AntiCsrf $anti_csrf, array $parameters = [])
    {
        $this->anti_csrf  = $anti_csrf;
        $this->parameters = $parameters;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->parameters);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->parameters);
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

        return $this->parameters[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     * @throws CouldNotGetCsrfToken
     */
    public function offsetSet($offset, $value): void
    {
        $this->addParam($offset, $value);
    }


    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->removeParam($offset);
    }

    /**
     * Add a parameter to the bag.
     *
     * @param string $parameter Parameter's name.
     * @param null   $value     Parameter's value (null if no need to validate, array for enumeration).
     *
     * @return $this
     * @throws CouldNotGetCsrfToken
     */
    public function addParam(string $parameter, $value = null): self
    {
        if (is_numeric($parameter)) {
            throw CouldNotGetCsrfToken::invalidParameter($parameter);
        }

        $this->parameters[$parameter] = $value;

        return $this;
    }

    /**
     * Add a bunch of parameters to the bag.
     *
     * @param array $parameters An array of key => value parameters (null values needed).
     *
     * @return $this
     * @throws CouldNotGetCsrfToken
     */
    public function addParams(array $parameters): self
    {
        foreach ($parameters as $_parameter => $_value) {
            $this->addParam($_parameter, $_value);
        }

        return $this;
    }

    /**
     * Flush the bag and set the given parameters.
     *
     * @param array $parameters An array of key => value parameters (null values needed).
     *
     * @return $this
     * @throws CouldNotGetCsrfToken
     */
    public function setParams(array $parameters): self
    {
        $this->flush();

        $this->addParams($parameters);

        return $this;
    }

    /**
     * Remove a given parameter from the bag.
     *
     * @param string $parameter The parameter's name.
     *
     * @return $this
     */
    public function removeParam(string $parameter): self
    {
        if ($this->offsetExists($parameter)) {
            unset($this->parameters[$parameter]);
        }

        return $this;
    }

    /**
     * Remove a parameter list from the bag.
     *
     * @param array $parameters An array of parameter names.
     *
     * @return $this
     */
    public function removeParams(array $parameters): self
    {
        foreach ($parameters as $_parameter) {
            $this->removeParam($_parameter);
        }

        return $this;
    }

    /**
     * Empty the parameter bag.
     *
     * @return $this
     */
    public function flush(): self
    {
        $this->parameters = [];

        return $this;
    }

    /**
     * Return the token according to parameter bag.
     *
     * @param int|null $ttl
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    public function getToken(?int $ttl = null): string
    {
        return $this->anti_csrf->getTokenFor($this->parameters, $ttl);
    }
}
