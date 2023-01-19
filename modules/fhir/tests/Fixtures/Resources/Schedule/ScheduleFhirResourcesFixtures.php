<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Schedule;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Schedule\CFHIRResourceSchedule;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resouces\Schedule
 */
class ScheduleFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_SCHEDULE_FHIR_RESOURCE = 'fhir_schedule_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceSchedule::class,
            'object_class'  => 'CPlageconsult',
            'fixture_ref'   => self::REF_SCHEDULE_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generateSchedule();
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
    private function generateSchedule(): void
    {
        $schedule          = FhirResourcesHelper::getSampleFhirPlageconsult();
        $schedule->chir_id = $this->getUser()->_id;
        $this->store($schedule, self::REF_SCHEDULE_FHIR_RESOURCE);
    }
}
