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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;

class CFHIRDataTypeCodeSystemConceptProperty extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CodeSystem.concept.property';

    public ?CFHIRDataTypeCode $code = null;

    public ?CFHIRDataTypeChoice $value = null;

    /** @var CFHIRDataTypeResource[]|null */
    public ?array $concept = null;
}
