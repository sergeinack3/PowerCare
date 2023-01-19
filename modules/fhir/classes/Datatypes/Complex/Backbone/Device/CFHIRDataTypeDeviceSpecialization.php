<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;

/**
 * FHIR data type
 */
class CFHIRDataTypeDeviceSpecialization extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Device.specialization';

    public ?CFHIRDataTypeCodeableConcept $systemType = null;

    public ?CFHIRDataTypeString $version = null;
}
