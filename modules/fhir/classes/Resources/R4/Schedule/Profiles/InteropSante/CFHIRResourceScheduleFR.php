<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Schedule\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Schedule\CFHIRResourceSchedule;

class CFHIRResourceScheduleFR extends CFHIRResourceSchedule
{
    /** @var string */
    public const PROFILE_TYPE = 'FrSchedule';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;
}
