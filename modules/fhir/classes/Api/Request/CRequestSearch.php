<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Request;

use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class CRequestSearch
{
    /** @var Request */
    private $request;

    /** @var CFHIRResource */
    private $resource;

    /** @var SearchParameterList */
    private $parameters;

    /**
     * CRequestSearch constructor.
     *
     * @param Request            $request
     * @param CFHIRResource|null $resource
     */
    public function __construct(Request $request, ?CFHIRResource $resource)
    {
        $this->request  = $request;
        $this->resource = $resource;

        $this->parameters = $this->findParameters();
        $this->applyParameters($this->parameters);
    }

    /**
     * @return SearchParameterList
     */
    private function findParameters(): ?SearchParameterList
    {
        $parameters = new SearchParameterList();
        if (!$this->resource) {
            return $parameters;
        }

        $request_parameters = $this->request->query;
        $capabilities       = $this->resource->getCapabilities();

        // for each parameter in query
        foreach ($request_parameters->all() as $key => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            // create parameter for each values
            foreach ($values as $value) {
                if (!$parameter = $this->createResourceParameter($capabilities, $key, $value)) {
                    continue;
                }

                $parameters->add($parameter);
            }
        }

        return $parameters;
    }

    /**
     * @param CCapabilitiesResource $capabilities
     * @param string                $key
     * @param mixed                 $value
     *
     * @return SearchParameter
     */
    private function createResourceParameter(CCapabilitiesResource $capabilities, string $key, $value): ?SearchParameter
    {
        $explode        = explode(':', $key);
        $parameter_name = $explode[0];
        $modifier       = $explode[1] ?? null;

        // find search parameter for this resource
        if (!$search_parameter_type = $capabilities->getSearchParameters($parameter_name)) {
            return null;
        }

        return new SearchParameter($search_parameter_type, $value, $modifier);
    }

    /**
     * @param SearchParameterList $parameters
     *
     * @return CFHIRResource|null
     */
    protected function applyParameters(SearchParameterList $parameters): ?CFHIRResource
    {
        if ($this->resource) {
            $this->resource->setParameterSearch($this->request->query);
            $this->resource->setParameterSearch(new ParameterBag($parameters->all()));
        }

        return $this->resource;
    }

    /**
     * @return SearchParameterList
     */
    public function getSearchParameters(): SearchParameterList
    {
        return $this->parameters;
    }
}
