<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Appointment;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypeAppointmentParticipant extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Appointment.participant';

    /** @var CFHIRDataTypeCodeableConcept[] */
    public $type;

    /** @var CFHIRDataTypeReference */
    public $actor;

    /** @var CFHIRDataTypeCode */
    public $required;

    /** @var CFHIRDataTypeCode */
    public $status;

    /** @var CFHIRDataTypePeriod */
    public $period;
}
