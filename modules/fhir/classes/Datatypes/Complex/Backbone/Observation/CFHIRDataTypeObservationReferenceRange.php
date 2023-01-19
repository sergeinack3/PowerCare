<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeRange;

/**
 * FHIR data type
 */
class CFHIRDataTypeObservationReferenceRange extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Observation.referenceRange';

    /** @var CFHIRDataTypeQuantity */
    public $low;

    /** @var CFHIRDataTypeQuantity */
    public $high;

    /** @var CFHIRDataTypeCodeableConcept */
    public $type;

    /** @var CFHIRDataTypeCodeableConcept */
    public $appliesTo;

    /** @var CFHIRDataTypeRange */
    public $age;

    /** @var CFHIRDataTypeString */
    public $text;
}
