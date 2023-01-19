<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location\Profiles\MES;

use Ox\Interop\Fhir\Profiles\CFHIRMES;
use Ox\Interop\Fhir\Resources\R4\Location\Profiles\InteropSante\CFHIRResourceLocationFR;

class CFHIRResourceLocationENS extends CFHIRResourceLocationFR
{
    /** @var string */
    public const PROFILE_TYPE = 'ENS_FrLocation';

    /** @var string */
    public const PROFILE_CLASS = CFHIRMES::class;
}
