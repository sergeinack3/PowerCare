<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Organization;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Organization\CFHIRResourceOrganization;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resources\Organization
 */
class OrganizationFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_ORGANIZATION_FHIR_RESOURCE = 'fhir_organization_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceOrganization::class,
            'object_class'  => 'CExercicePlace',
            'fixture_ref'   => self::REF_ORGANIZATION_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        // ExercicePlace Organization
        $this->generateExercicePlaceOrganization();
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
     */
    private function generateExercicePlaceOrganization(): CExercicePlace
    {
        /** @var CExercicePlace $organization */
        $organization                 = CExercicePlace::getSampleObject();
        $organization->raison_sociale = 'FHIRPLACE';
        $organization->finess         = '111111111';
        $organization->tel            = '0102030401';
        $organization->tel2           = '0102030402';
        $organization->fax            = '0102030403';
        $organization->email          = 'mail@mail.com';
        $organization->adresse        = '1 rue de fhir';
        $organization->commune        = 'FHIRLAND';
        $organization->cp             = '01010';
        $organization->pays           = 'France';
        $this->store($organization, self::REF_ORGANIZATION_FHIR_RESOURCE);

        return $organization;
    }
}
