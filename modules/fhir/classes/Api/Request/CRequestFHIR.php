<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Request;

use Exception;
use Ox\Core\Api\Request\Content\RequestContent;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CRequestFHIR
{
    use RequestHelperTrait;

    /** @var CRequestFormats */
    protected $request_format;

    /** @var CRequestResource */
    protected $request_resource;

    /** @var CRequestSearch */
    protected $request_search;

    /** @var RequestContent */
    protected $request_content;

    /** @var CRequestInteraction */
    protected $request_interaction;

    /**
     * CRequestFHIR constructor.
     *
     * @param RequestStack $request
     *
     * @throws Exception
     */
    public function __construct(RequestStack $request)
    {
        $request                   = $request->getCurrentRequest();
        $this->request             = $request;
        $this->request_format      = new CRequestFormats($request);
        $this->request_resource    = new CRequestResource($request);
        $this->request_search      = new CRequestSearch($request, $this->getResource());
        $this->request_content     = new RequestContent($request);
        $this->request_interaction = new CRequestInteraction($request);
    }

    /**
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @return CRequestResource|null
     * @throws Exception
     */
    public function getResource(): ?CFHIRResource
    {
        return $this->request_resource->getResource();
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->request_format->getFormat();
    }

    public function getSender(): ?CSenderFHIR
    {
        return $this->request_resource->getSender();
    }

    /**
     * @return string
     */
    public function getResourceVersion(): ?string
    {
        return $this->request_resource->getVersionId();
    }

    /**
     * @return string|null
     */
    public function getResourceId(): ?string
    {
        return $this->request_resource->getResourceId();
    }

    /**
     * @return CFHIRInteraction|null
     * @throws Exception
     */
    public function getInteraction(): ?CFHIRInteraction
    {
        $interaction = ($this->request_interaction->getInteraction())
            ->setFormat($this->getFormat());

        if ($resource = $this->getResource()) {
            $interaction->setResource($resource);
        }

        $interaction->_ref_fhir_exchange = CFHIRController::$exchange_fhir;

        return $interaction;
    }

    /**
     * @return SearchParameterList
     */
    public function getSearchParameters(): SearchParameterList
    {
        return $this->request_search->getSearchParameters();
    }

    /**
     * @param bool   $json_decode
     * @param string $encode_to
     * @param string $encode_from
     *
     * @return false|mixed|resource|string|null
     * @throws Exception
     */
    public function getContent(bool $json_decode = false, string $encode_to = null, string $encode_from = 'UTF-8')
    {
        return $this->request_content->getContent($json_decode, $encode_to, $encode_from);
    }
}
