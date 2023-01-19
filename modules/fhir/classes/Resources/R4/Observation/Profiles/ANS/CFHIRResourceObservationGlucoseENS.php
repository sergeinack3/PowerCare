<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS;

use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Profiles\CFHIRMES;
use Ox\Interop\Fhir\Resources\R4\Observation\CFHIRResourceObservation;
use Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS\ENSObservationGlucose;
use Ox\Mediboard\Patients\Constants\CValueInt;

/**
 * Description
 */
class CFHIRResourceObservationGlucoseENS extends CFHIRResourceObservation
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'ENS_FrObservationGlucose';

    /** @var string */
    public const PROFILE_CLASS = CFHIRMES::class;

    /**
     * @param CStoredObject $object
     *
     * @return DelegatedObjectMapperInterface
     * @throws CFHIRExceptionNotSupported
     */
    protected function setMapperOld(CStoredObject $object): DelegatedObjectMapperInterface
    {
        if (get_class($object) !== CValueInt::class) {
            throw new CFHIRExceptionNotSupported("Object value type is not correct !");
        }

        $ref_spec = $object->getRefSpec();

        if ($ref_spec->code !== 'glucose') {
            throw new CFHIRExceptionNotSupported("Abstract constant is not a glucose measurement");
        }

        $mapping_object = ENSObservationGlucose::class;

        return new $mapping_object();
    }
}
