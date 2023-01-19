<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypeEncounterDiagnosis extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Encounter.diagnosis';

    public ?CFHIRDataTypeReference $condition = null;

    public ?CFHIRDataTypeCodeableConcept $use = null;

    public ?CFHIRDataTypePositiveInt $rank = null;
}
