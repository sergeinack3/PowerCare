<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Slot\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Slot\CFHIRResourceSlot;

class CFHIRResourceSlotFR extends CFHIRResourceSlot
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = "FrSlot";

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;
}
