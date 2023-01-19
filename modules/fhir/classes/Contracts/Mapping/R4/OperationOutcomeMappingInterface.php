<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome\CFHIRDataTypeOperationOutcomeIssue;

/**
 * Description
 */
interface OperationOutcomeMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "OperationOutcome";

    /**
     * @return CFHIRDataTypeOperationOutcomeIssue[]
     */
    public function mapIssue(): array;
}
