<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;

class CFHIRDataTypeCodeSystemConceptDesignation extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CodeSystem.concept.designation';

    public ?CFHIRDataTypeCode $language = null;

    public ?CFHIRDataTypeCoding $use = null;

    public ?CFHIRDataTypeString $value = null;
}
