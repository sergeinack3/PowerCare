<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Slot;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Slot\CFHIRResourceSlot;
use Ox\Interop\Fhir\Tests\Fixtures\FhirApiFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesTrait;
use Ox\Mediboard\Cabinet\CSlot;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resources\Slot
 */
class SlotFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_SLOT_FHIR_RESOURCE = 'fhir_slot_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceSlot::class,
            'object_class'  => 'CSlot',
            'fixture_ref'   => self::REF_SLOT_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        // Slot
        $this->generateSlot();
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
    private function generateSlot(): CSlot
    {
        $fhir_api_fixtures = new FhirApiFixtures();
        $sejour            = $fhir_api_fixtures->generateSejour();

        $plageconsult          = FhirResourcesHelper::getSampleFhirPlageconsult();
        $plageconsult->chir_id = $this->getUser()->_id;
        $this->store($plageconsult);

        $consultation                  = FhirResourcesHelper::getSampleFhirConsultation();
        $consultation->plageconsult_id = $plageconsult->_id;
        $consultation->sejour_id       = $sejour->_id;
        $this->store($consultation);

        $slot                  = FhirResourcesHelper::getSampleFhirSlot();
        $slot->plageconsult_id = $plageconsult->_id;
        $slot->consultation_id = $consultation->_id;
        $slot->overbooked      = 1;
        $this->store($slot, self::REF_SLOT_FHIR_RESOURCE);

        return $slot;
    }
}
