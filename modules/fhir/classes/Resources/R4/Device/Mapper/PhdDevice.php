<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Device\Mapper;

use Ox\Interop\Ihe\CPHD;

/**
 * Description
 */
class PhdDevice extends Device
{
    /**
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CPHD::class];
    }
}
