<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Slot\Mapper;

use Exception;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Schedule\Profiles\InteropSante\CFHIRResourceScheduleFR;
use Ox\Interop\Fhir\Resources\R4\Slot\Profiles\InteropSante\CFHIRResourceSlotFR;
use Ox\Mediboard\Cabinet\CSlot;

/**
 * Description
 */
class FrSlot extends Slot
{
    /** @var CSlot */
    protected $object;

    /** @var CFHIRResourceSlotFR */
    protected CFHIRResource $resource;

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    public function mapSchedule(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourceScheduleFR::class, $this->object->loadRefPlageconsult());
    }

    /**
     * @throws Exception
     */
    public function mapSpecialty(): array
    {
        $plage_consult = $this->object->loadRefPlageconsult();

        $practitioner = $plage_consult->loadRefChir();

        if ($practitioner && $practitioner->_id) {
            $coding = $this->resource->setPractitionerSpecialty($practitioner);

            return [CFHIRDataTypeCodeableConcept::addCodeable($coding)];
        }

        return [];
    }
}
