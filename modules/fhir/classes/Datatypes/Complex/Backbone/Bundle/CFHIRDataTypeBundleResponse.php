<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome\CFHIRDataTypeOperationOutcomeIssue;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Resources\R4\OperationOutcome\CFHIRResourceOperationOutcome;

/**
 * Class CFHIRDataTypeBundleResponse
 * @package Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle
 */
class CFHIRDataTypeBundleResponse extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Bundle.entry.response';

    /** @var CFHIRDataTypeString */
    public $status;

    /** @var CFHIRDataTypeUri */
    public $location;

    /** @var CFHIRDataTypeString */
    public $etag;

    /** @var CFHIRDataTypeInstant */
    public $lastModified;

    /** @var CFHIRDataTypeResource [CFHIRResourceOperationOutcome] */
    public $outcome;

    /**
     * Set property status
     *
     * @param CFHIRDataTypeString $status
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setStatusElement(CFHIRDataTypeString $status): CFHIRDataTypeBundleResponse
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set property status
     *
     * @param string $status
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setStatus(string $status): CFHIRDataTypeBundleResponse
    {
        return $this->setStatusElement(new CFHIRDataTypeString($status));
    }

    /**
     * Set property location
     *
     * @param CFHIRDataTypeUri $location
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setLocationElement(CFHIRDataTypeUri $location): CFHIRDataTypeBundleResponse
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set property location
     *
     * @param string $location
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setLocation(string $location): CFHIRDataTypeBundleResponse
    {
        return $this->setLocationElement(new CFHIRDataTypeUri($location));
    }

    /**
     * Set property etag
     *
     * @param CFHIRDataTypeString $etag
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setEtagElement(CFHIRDataTypeString $etag): CFHIRDataTypeBundleResponse
    {
        $this->etag = $etag;

        return $this;
    }

    /**
     * Set property etag
     *
     * @param CFHIRDataTypeString $etag
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setEtag(CFHIRDataTypeString $etag): CFHIRDataTypeBundleResponse
    {
        return $this->setEtagElement(new CFHIRDataTypeString($etag));
    }

    /**
     * Set property lastModified
     *
     * @param CFHIRDataTypeInstant $lastModified
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setLastModifiedElement(CFHIRDataTypeInstant $lastModified): CFHIRDataTypeBundleResponse
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * Set property lastModified
     *
     * @param string $lastModified
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setLastModified(string $lastModified): CFHIRDataTypeBundleResponse
    {
        return $this->setLastModifiedElement(new CFHIRDataTypeInstant($lastModified));
    }

    /**
     * Set property outcome
     *
     * @param CFHIRDataTypeResource $outcome
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setOutcomeElement(CFHIRDataTypeResource $outcome): CFHIRDataTypeBundleResponse
    {
        $this->outcome = $outcome;

        return $this;
    }

    /**
     * Set property outcome
     *
     * @param CFHIRResourceOperationOutcome $outcome
     *
     * @return CFHIRDataTypeBundleResponse
     */
    public function setOutcome(CFHIRResourceOperationOutcome $outcome): CFHIRDataTypeBundleResponse
    {
        return $this->setOutcomeElement(new CFHIRDataTypeResource($outcome));
    }

    /**
     * Add issue
     *
     * @param CFHIRDataTypeOperationOutcomeIssue $issue
     *
     * @return $this
     */
    public function addIssue(CFHIRDataTypeOperationOutcomeIssue $issue)
    {
        if (!$this->outcome) {
            $this->setOutcome(new CFHIRResourceOperationOutcome());
        }

        /** @var CFHIRResourceOperationOutcome $resource */
        $resource = $this->outcome->_value;
        $resource->addIssue($issue);

        return $this;
    }
}

