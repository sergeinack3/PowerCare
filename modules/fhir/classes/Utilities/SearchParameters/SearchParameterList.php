<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\CMbArray;

class SearchParameterList
{
    /** @var ISearchParameter[] */
    private $parameters = [];

    /**
     * SearchParameterList constructor.
     *
     * @param ISearchParameter[]|null $parameters
     */
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof ISearchParameter) {
                continue;
            }

            $this->add($parameter);
        }
    }

    /**
     * @param ISearchParameter ...$search_parameter
     *
     * @return bool
     */
    public function add(ISearchParameter ...$search_parameter): bool
    {
        $this->parameters = array_merge($this->parameters, $search_parameter);

        return true;
    }

    public function addUnique(ISearchParameter $search_parameter): bool
    {
        if ($this->has($search_parameter->getParameterName())) {
            return false;
        }

        return $this->add($search_parameter);
    }

    /**
     * @param string $parameter_name
     *
     * @return bool
     */
    public function has(string $parameter_name): bool
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->getParameterName() === $parameter_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $parameter_name
     * @param int    $position
     *
     * @return ISearchParameter|null
     */
    public function get(string $parameter_name, int $position = 0): ?ISearchParameter
    {
        return CMbArray::get($this->all($parameter_name), $position);
    }

    /**
     * @return ISearchParameter[]
     */
    public function all(?string $parameter_name = null): array
    {
        if ($parameter_name) {
            $parameters = array_filter(
                $this->parameters,
                function ($parameter) use ($parameter_name) {
                    return $parameter->getParameterName() === $parameter_name;
                }
            );

            return array_values($parameters);
        }

        return $this->parameters;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $parameters = [];

        foreach ($this->parameters as $key => $search_parameter) {
            if ($search_parameter instanceof SearchParameter) {
                $parameters[$search_parameter->getParameterName()] = $search_parameter->getValue();
            } else {
                $parameters[$key] = $search_parameter->getParameterName();
            }
        }

        return $parameters;
    }
}
