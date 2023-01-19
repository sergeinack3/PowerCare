<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location\Mapper;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;

/**
 * Description
 */
class FrLocation extends Location
{
    use CStoredObjectResourceTrait;

    /**
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }
}
