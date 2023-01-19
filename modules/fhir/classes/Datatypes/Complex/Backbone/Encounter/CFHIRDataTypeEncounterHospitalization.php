<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypeEncounterHospitalization extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Encounter.hospitalization';

    public ?CFHIRDataTypeIdentifier $preAdmissionIdentifier = null;

    public ?CFHIRDataTypeReference $origin = null;

    public ?CFHIRDataTypeCodeableConcept $admitSource = null;

    public ?CFHIRDataTypeCodeableConcept $reAdmission = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    public array $dietPreference = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    public array $specialCourtesy = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    public array $specialArrangement = [];

    public ?CFHIRDataTypeReference $destination = null;

    public ?CFHIRDataTypeCodeableConcept $dischargeDisposition = null;
}
