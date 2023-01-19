<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Practitioner;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class PractitionerFhirResourcesFixtures
 */
class PractitionerFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_PRACTITIONER_FHIR_RESOURCE = 'fhir_practitioner_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourcePractitioner::class,
            'object_class'  => 'CMedecin',
            'fixture_ref'   => self::REF_PRACTITIONER_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generatePractitioner();
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
    private function generatePractitioner(): void
    {
        $medecin = FhirResourcesHelper::getSampleFhirMedecin();
        $this->store($medecin, self::REF_PRACTITIONER_FHIR_RESOURCE);
    }
}
