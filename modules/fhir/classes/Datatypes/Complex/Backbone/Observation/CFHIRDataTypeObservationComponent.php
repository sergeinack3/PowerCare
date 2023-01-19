<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;

/**
 * Class CFHIRDataTypeComponent
 * @package Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation
 */
class CFHIRDataTypeObservationComponent extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Observation.component';

    /** @var CFHIRDataTypeCodeableConcept */
    public CFHIRDataTypeCodeableConcept $code;

    /** @var CFHIRDataTypeChoice|null */
    public ?CFHIRDataTypeChoice $value = null;

    /** @var CFHIRDataTypeCodeableConcept|null */
    public ?CFHIRDataTypeCodeableConcept $dataAbsentReason = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    public array $interpretation = [];

    /** @var CFHIRDataTypeObservationReferenceRange[] */
    public array $referenceRange = [];
}
