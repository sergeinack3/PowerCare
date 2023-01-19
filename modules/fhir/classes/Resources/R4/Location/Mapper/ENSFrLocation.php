<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location\Mapper;

use Ox\Interop\Fhir\Profiles\CFHIRMES;

/**
 * Description
 */
class ENSFrLocation extends FrLocation
{
    public function onlyProfiles(): array
    {
        return [CFHIRMES::class];
    }
}
