<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\AllergyIntolerance;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;

/**
 * Class CFHIRDataTypeAllergyIntoleranceReaction
 * @package Ox\Interop\Fhir\Datatypes\Complex\Backbone\AllergyIntolerance
 */
class CFHIRDataTypeAllergyIntoleranceReaction extends CFHIRDataTypeBackboneElement
{
    public const NAME = 'AllergyIntolerance.reaction';

    /** @var CFHIRDataTypeCodeableConcept */
    public $substance;

    /** @var CFHIRDataTypeCodeableConcept[]  */
    public $manifestation;

    /** @var CFHIRDataTypeString */
    public $description;

    /** @var CFHIRDataTypeDateTime */
    public $onset;

    /** @var CFHIRDataTypeCode */
    public $severity;

    /** @var CFHIRDataTypeCodeableConcept */
    public $exposureRoute;

    /** @var CFHIRDataTypeAnnotation[] */
    public $note;
}

