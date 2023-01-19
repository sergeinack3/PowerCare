<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Appointment\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Appointment\CFHIRResourceAppointment;
use Ox\Interop\Fhir\Resources\R4\Appointment\Mapper\FrLocation;
use Ox\Interop\Fhir\Resources\R4\Slot\Profiles\InteropSante\CFHIRResourceSlotFR;

class CFHIRResourceAppointmentFR extends CFHIRResourceAppointment
{
    /** @var string */
    public const PROFILE_TYPE = 'FrAppointment';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;

    /**
     * Map property slot
     */
    protected function mapSlot(string $resource_class = CFHIRResourceSlotFR::class): void
    {
        $this->slot = $this->object_mapping->mapSlot($resource_class);
    }
}
