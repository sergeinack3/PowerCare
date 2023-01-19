<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypePractitionerRoleAvailableTime extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'PractitionerRole.availableTime';

    /** @var CFHIRDataTypeCode[] */
    public $daysOfWeek;

    /** @var CFHIRDataTypeBoolean */
    public $allDay;

    /** @var CFHIRDataTypeTime */
    public $availableStartTime;

    /** @var CFHIRDataTypeTime */
    public $availableEndTime;
}
