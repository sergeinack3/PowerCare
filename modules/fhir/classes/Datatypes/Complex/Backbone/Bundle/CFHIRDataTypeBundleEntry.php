<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeSignature;

class CFHIRDataTypeBundleEntry extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Bundle.entry';

    /** @var CFHIRDataTypeUri[] */
    public $link;

    /** @var CFHIRDataTypeUri */
    public $fullUrl;

    /** @var CFHIRDataTypeResource */
    public $resource;

    /** @var CFHIRDataTypeBundleSearch */
    public $search;

    /** @var CFHIRDataTypeBundleRequest */
    public $request;

    /** @var CFHIRDataTypeBundleResponse */
    public $response;

    /** @var CFHIRDataTypeSignature */
    public $signature;

    /**
     * Set property link
     *
     * @param array $links
     *
     * @return self
     */
    public function setLinkElement(array $links): self
    {
        $links_uri = [];
        foreach ($links as $link) {
            if (is_string($link)) {
                $link = new CFHIRDataTypeUri($link);
            }

            if ($link instanceof CFHIRDataTypeUri) {
                $links_uri[] = $link;
            }
        }

        $this->link = $links_uri;

        return $this;
    }

    /**
     * Set property link
     *
     * @param string|CFHIRDataTypeUri $link
     *
     * @return self
     */
    public function addLinkElement($link): self
    {
        if (is_string($link)) {
            $link = new CFHIRDataTypeUri($link);
        }

        if ($link instanceof CFHIRDataTypeUri) {
            $this->link[] = $link;
        }

        return $this;
    }


    /**
     * Set property fullUrl
     *
     * @param string $fullUrl
     *
     * @return self
     */
    public function setFullUrl(string $fullUrl): self
    {
        return $this->setFullUrlElement(new CFHIRDataTypeUri($fullUrl));
    }

    /**
     * Set property fullUrl
     *
     * @param CFHIRDataTypeUri $fullUrl
     *
     * @return self
     */
    public function setFullUrlElement(CFHIRDataTypeUri $fullUrl): self
    {
        $this->fullUrl = $fullUrl;

        return $this;
    }

    /**
     * Set property resource
     *
     * @param CFHIRDataTypeResource $resource
     *
     * @return self
     */
    public function setResourceElement(CFHIRDataTypeResource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Set property search
     *
     * @param CFHIRDataTypeBundleSearch $search
     *
     * @return self
     */
    public function setSearchElement(CFHIRDataTypeBundleSearch $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Set property request
     *
     * @param CFHIRDataTypeBundleRequest $request
     *
     * @return self
     */
    public function setRequestElement(CFHIRDataTypeBundleRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set property response
     *
     * @param CFHIRDataTypeBundleResponse $response
     *
     * @return self
     */
    public function setResponseElement(CFHIRDataTypeBundleResponse $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return CFHIRDataTypeResource|null
     */
    public function getResource(): ?CFHIRDataTypeResource
    {
        return $this->resource;
    }
}

