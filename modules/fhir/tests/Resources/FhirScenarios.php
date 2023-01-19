<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Resources;

use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Tests\OxTestTrait;

abstract class FhirScenarios
{
    use OxTestTrait;

    /**
     * @return CFHIRResource
     */
    final public function getResourcesScenario(): array
    {
        return $this->createFhirResources();
    }

    abstract protected function createFhirResources(): array;
}
