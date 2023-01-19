<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;

/**
 * FHIR data type
 */
class CFHIRDataTypePatientCommunication extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Patient.communication';

    /** @var CFHIRDataTypeCodeableConcept */
    public $language;

    /** @var CFHIRDataTypeBoolean */
    public $preferred;
}
