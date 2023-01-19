<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * Class CFHIRDataTypeBundleRequest
 * @package Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle
 */
class CFHIRDataTypeBundleRequest extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Bundle.entry.request';

    /** @var CFHIRDataTypeCode */
    public $method;

    /** @var CFHIRDataTypeUri */
    public $url;

    /** @var CFHIRDataTypeString */
    public $ifNoneMatch;

    /** @var CFHIRDataTypeInstant */
    public $ifModifiedSince;

    /** @var CFHIRDataTypeString */
    public $ifMatch;

    /** @var CFHIRDataTypeString */
    public $ifNoneExist;

    /**
     * @param CFHIRDataTypeCode $method
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setMethodElement(CFHIRDataTypeCode $method): CFHIRDataTypeBundleRequest
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setMethod(string $method): CFHIRDataTypeBundleRequest
    {
        return $this->setMethodElement(new CFHIRDataTypeCode($method));
    }

    /**
     * @param CFHIRDataTypeUri $ur
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setUrlElement(CFHIRDataTypeUri $ur): CFHIRDataTypeBundleRequest
    {
        $this->url = $ur;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setUrl(string $url): CFHIRDataTypeBundleRequest
    {
        return $this->setUrlElement(new CFHIRDataTypeUri($url));
    }

    /**
     * @param CFHIRDataTypeString $ifNoneMatch
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfNoneMatchElement(CFHIRDataTypeString $ifNoneMatch): CFHIRDataTypeBundleRequest
    {
        $this->ifNoneMatch = $ifNoneMatch;

        return $this;
    }

    /**
     * @param string $ifNoneMatch
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfNoneMatch(string $ifNoneMatch): CFHIRDataTypeBundleRequest
    {
        return $this->setIfNoneMatchElement(new CFHIRDataTypeString($ifNoneMatch));
    }

    /**
     * @param CFHIRDataTypeInstant $ifModifiedSince
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfModifiedSinceElement(CFHIRDataTypeInstant $ifModifiedSince): CFHIRDataTypeBundleRequest
    {
        $this->ifModifiedSince = $ifModifiedSince;

        return $this;
    }

    /**
     * @param string $ifModifiedSince
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfModifiedSince(string $ifModifiedSince): CFHIRDataTypeBundleRequest
    {
        return $this->setIfModifiedSinceElement(new CFHIRDataTypeInstant($ifModifiedSince));
    }

    /**
     * @param CFHIRDataTypeString $ifMatch
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfMatchElement(CFHIRDataTypeString $ifMatch): CFHIRDataTypeBundleRequest
    {
        $this->ifMatch = $ifMatch;

        return $this;
    }

    /**
     * @param string $ifMatch
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfMatch(string $ifMatch): CFHIRDataTypeBundleRequest
    {
        return $this->setIfMatchElement(new CFHIRDataTypeString($ifMatch));
    }

    /**
     * @param CFHIRDataTypeString $ifNoneExist
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfNoneExistElement(CFHIRDataTypeString $ifNoneExist): CFHIRDataTypeBundleRequest
    {
        $this->ifNoneExist = $ifNoneExist;

        return $this;
    }

    /**
     * @param string $ifNoneExist
     *
     * @return CFHIRDataTypeBundleRequest
     */
    public function setIfNoneExist(string $ifNoneExist): CFHIRDataTypeBundleRequest
    {
        return $this->setIfNoneExistElement(new CFHIRDataTypeString($ifNoneExist));
    }

}

