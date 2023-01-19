<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

class CFHIRDataTypeCodeSystemConcept extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CodeSystem.concept';

    public ?CFHIRDataTypeCode $code = null;

    public ?CFHIRDataTypeString $display = null;

    public ?CFHIRDataTypeString $definition = null;

    /** @var CFHIRDataTypeCodeSystemConceptDesignation[]|null  */
    public ?array $designation = null;

    /** @var CFHIRDataTypeCodeSystemConceptProperty[]|null */
    public ?array $property = null;
}

