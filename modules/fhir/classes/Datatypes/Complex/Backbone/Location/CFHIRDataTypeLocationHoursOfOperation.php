<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeLocationHoursOfOperation extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Location.hoursOfOperation';

    /** @var CFHIRDataTypeCode */
    public $daysOfWeek;

    /** @var CFHIRDataTypeBoolean */
    public $allDay;

    /** @var CFHIRDataTypeTime */
    public $openingTime;

    /** @var CFHIRDataTypeTime */
    public $closingTime;
}
