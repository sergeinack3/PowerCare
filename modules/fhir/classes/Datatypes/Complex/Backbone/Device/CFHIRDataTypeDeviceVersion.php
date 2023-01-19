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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;

/**
 * FHIR data type
 */
class CFHIRDataTypeDeviceVersion extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Device.version';

    public ?CFHIRDataTypeCodeableConcept $type = null;

    public ?CFHIRDataTypeIdentifier $component = null;

    public ?CFHIRDataTypeString $value = null;
}
