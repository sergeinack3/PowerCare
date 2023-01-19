<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypePatientContact extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Patient.contact';

    /** @var CFHIRDataTypeCodeableConcept[] */
    public $relationship;

    /** @var CFHIRDataTypeHumanName */
    public $name;

    /** @var CFHIRDataTypeContactPoint[] */
    public $telecom;

    /** @var CFHIRDataTypeAddress */
    public $address;

    /** @var CFHIRDataTypeCode */
    public $gender;

    /** @var CFHIRDataTypeReference */
    public $organization;
}
