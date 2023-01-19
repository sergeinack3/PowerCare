<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Appointment\Mapper;

use Exception;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Appointment\Profiles\InteropSante\CFHIRResourceAppointmentFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Mediboard\Cabinet\CConsultation;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class FrAppointment extends Appointment
{
    /** @var CConsultation */
    protected $object;

    /** @var CFHIRResourceAppointmentFR */
    protected CFHIRResource $resource;

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceAppointmentFR::class];
    }

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapExtension(): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                'http://interopsante.org/fhir/StructureDefinition/FrAppointmentOperator',
                [
                    'value' => $this->resource->addReference(
                        CFHIRResourcePractitionerFR::class,
                        $this->object->loadRefPraticien()
                    ),
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function mapSpecialty(): array
    {
        $practitioner = $this->object->loadRefPraticien();

        if ($practitioner && $practitioner->_id) {
            $coding = $this->resource->setPractitionerSpecialty($practitioner);

            if (!empty($coding)) {
                return [CFHIRDataTypeCodeableConcept::addCodeable($coding)];
            }
        }

        return [];
    }

    public function mapSlot(string $resource_class): array
    {
        $slots = [];

        $refs_slots = $this->object->loadRefSlots();

        foreach ($refs_slots as $_slot) {
            $slots[] = $this->resource->addReference($resource_class, $_slot);
        }

        return $slots;
    }
}
