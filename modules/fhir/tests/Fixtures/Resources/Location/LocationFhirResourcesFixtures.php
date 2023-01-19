<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Location;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Location\CFHIRResourceLocation;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resources\Location
 */
class LocationFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_LOCATION_FHIR_RESOURCE = 'fhir_location_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceLocation::class,
            'object_class'  => 'CExercicePlace',
            'fixture_ref'   => self::REF_LOCATION_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generateExercicePlaceLocation();
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['fhir_resources'];
    }

    /**
     * @return CExercicePlace
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function generateExercicePlaceLocation(): void
    {
        /** @var CExercicePlace $location */
        $location                 = CExercicePlace::getSampleObject();
        $location->raison_sociale = 'FHIRPLACELOCATION';
        $location->tel            = '0102030404';
        $location->tel2           = '0102030405';
        $location->fax            = '0102030406';
        $location->email          = 'mail@mail.com';
        $location->adresse        = '1 rue de fhir';
        $location->commune        = 'FHIRLAND';
        $location->cp             = '01010';
        $location->pays           = 'France';
        $this->store($location, self::REF_LOCATION_FHIR_RESOURCE);
    }
}
