<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\PractitionerRole;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resources\PractitionerRole
 */
class PractitionerRoleFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDIUSER = 'fhir_practitioner_role_resource_mediuser';
    public const  REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDECIN  = 'fhir_practitioner_role_resource_medecin';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourcePractitionerRole::class,
            'object_class'  => 'CMedecin',
            'fixture_ref'   => self::REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDECIN,
        ],
        [
            'fhir_resource' => CFHIRResourcePractitionerRole::class,
            'object_class'  => 'CMediusers',
            'fixture_ref'   => self::REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDIUSER,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generatePractitionerRole();
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
    private function generatePractitionerRole(): void
    {
        $group = FhirResourcesHelper::getSampleFhirGroups();
        $this->store($group);

        $function = FhirResourcesHelper::getSampleFhirFunctions($group);
        $this->store($function);

        $mediuser               = $this->getUser();
        $mediuser->rpps         = CMbString::createLuhn(12337738323);
        $mediuser->function_id  = $function->_id;
        $mediuser->deb_activite = CMbDT::date('2000-01-01');
        $mediuser->fin_activite = CMbDT::date('2020-01-01');
        $this->store($mediuser, self::REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDIUSER);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generatePractitioner(): void
    {
        $medecin       = FhirResourcesHelper::getSampleFhirMedecin();
        $medecin->rpps = CMbString::createLuhn(12337738323);
        $this->store($medecin, self::REF_PRACTITIONER_ROLE_FHIR_RESOURCE_MEDECIN);
    }
}
