<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;

/**
 * FHIR data type
 */
class CFHIRDataTypeOperationOutcomeIssue extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'OperationOutcome.issue';

    /** @var CFHIRDataTypeCode */
    public $severity;

    /** @var CFHIRDataTypeCode */
    public $code;

    /** @var CFHIRDataTypeCodeableConcept */
    public $details;

    /** @var CFHIRDataTypeString */
    public $diagnostics;

    /** @var CFHIRDataTypeString[] */
    public $expression;
}
