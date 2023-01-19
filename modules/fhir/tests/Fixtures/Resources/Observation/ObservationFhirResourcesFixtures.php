<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Observation;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Observation\CFHIRResourceObservation;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class ObservationFhirResourcesFixtures
 */
class ObservationFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_OBSERVATION_FHIR_RESOURCE = 'fhir_observation_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceObservation::class,
            'object_class'  => 'CValueInt',
            'fixture_ref'   => self::REF_OBSERVATION_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generateValueInt();
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['fhir_resources'];
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     * @throws Exception
     */
    private function generateValueInt(): void
    {
        $patient = FhirResourcesHelper::getSamplePatient();
        $this->store($patient);

        $releve             = FhirResourcesHelper::getSampleFhirConstantReleve();
        $releve->patient_id = $patient->_id;
        $releve->user_id    = $this->getUser()->_id;
        $this->store($releve);

        $value_int             = FhirResourcesHelper::getSampleFhirValueInt();
        $value_int->releve_id  = $releve->_id;
        $value_int->patient_id = $patient->_id;
        $value_int->value      = 180;
        $this->store($value_int, self::REF_OBSERVATION_FHIR_RESOURCE);
    }
}
