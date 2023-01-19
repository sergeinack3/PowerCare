<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

class CFHIRDataTypeBundleLink extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Bundle.link';

    /** @var CFHIRDataTypeString */
    public $relation;

    /** @var CFHIRDataTypeUri */
    public $url;

    /**
     * Set property relation
     *
     * @param string $relation
     *
     * @return $this
     */
    public function setRelation(string $relation): self
    {
        return $this->setUrlRelation(new CFHIRDataTypeString($relation));
    }

    /**
     * @param CFHIRDataTypeString $relation
     *
     * @return $this
     */
    public function setUrlRelation(CFHIRDataTypeString $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Set property Url
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        return $this->setUrlElement(new CFHIRDataTypeUri($url));
    }

    /**
     * Set property Url
     *
     * @param CFHIRDataTypeUri $url
     *
     * @return $this
     */
    public function setUrlElement(CFHIRDataTypeUri $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getRelation(): ?CFHIRDataTypeString
    {
        return $this->relation;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getUrl(): ?CFHIRDataTypeUri
    {
        return $this->url;
    }
}

