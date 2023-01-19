<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Location\CFHIRResourceLocation;

class CFHIRResourceLocationFR extends CFHIRResourceLocation
{
    /** @var string */
    public const PROFILE_TYPE = 'FrLocation';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;
}
