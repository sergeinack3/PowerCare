<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

class CFHIRDataTypeCodeSystemFilter extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CodeSystem.filter';

    public ?CFHIRDataTypeCode $code = null;

    public ?CFHIRDataTypeString $description = null;

    public ?CFHIRDataTypeCode $operator = null;

    public ?CFHIRDataTypeString $value = null;
}

