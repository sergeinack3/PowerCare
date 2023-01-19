<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\Helper;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Mediboard\Loinc\CLoinc;

class ResourceHelper
{
    /**
     * Search Resource identifier (RI)
     *
     * @param CFHIRDomainResource $resource
     *
     * @return string|null
     * @throw CFHIRException
     */
    public static function getResourceIdentifier(CFHIRDomainResource $resource): ?string
    {
        $system_RI = 'http://terminology.hl7.org/CodeSystem/v2-0203';
        $code_RI   = 'RI';

        foreach ($resource->getIdentifier() as $identifier) {
            if ($identifier->type && $identifier->type->getCoding($system_RI, $code_RI)) {
                return $identifier->value->getValue();
            }
        }

        return null;
    }

    /**
     * Search identifier for system
     *
     * @param CFHIRDomainResource $resource
     * @param string              $system
     *
     * @return string|null
     * @throw CFHIRException
     */
    public static function getIdentifierForSystem(CFHIRDomainResource $resource, string $system): ?string
    {
        foreach ($resource->getIdentifier() as $identifier) {
            $id = $identifier->value->getValue();
            if ($identifier->isSystemMatch($system) && $id) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @param string $system
     *
     * @return bool
     */
    public static function isLoincSystem(CFHIRDataTypeUri $datatype_uri): bool
    {
        if (!$system = $datatype_uri->getValue()) {
            return false;
        }

        return $system === CLoinc::$system_loinc || $datatype_uri->isSystemMatch(CLoinc::$oid_loinc);
    }
}
