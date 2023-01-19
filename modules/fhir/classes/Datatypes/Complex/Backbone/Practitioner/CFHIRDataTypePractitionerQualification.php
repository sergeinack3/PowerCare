<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Practitioner;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypePractitionerQualification extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Practitioner.qualification';

    /** @var CFHIRDataTypeIdentifier[] */
    public $identifier;

    /** @var CFHIRDataTypeCodeableConcept */
    public $code;

    /** @var CFHIRDataTypePeriod */
    public $period;

    /** @var CFHIRDataTypeReference */
    public $issuer;
}
