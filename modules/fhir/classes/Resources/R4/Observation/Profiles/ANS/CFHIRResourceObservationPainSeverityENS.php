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
use Ox\Mediboard\Patients\Constants\CValueInt;

/**
 * Description
 */
class CFHIRResourceObservationPainSeverityENS extends CFHIRResourceObservation
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'ENS_FrObservationPainSeverity';

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

        if ($ref_spec->code !== 'painseverity') {
            throw new CFHIRExceptionNotSupported("Abstract constant is not a pain severity");
        }

        $mapping_object = ENSObservationPainSeverity::class;

        return new $mapping_object();
    }
}
