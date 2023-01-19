<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;

/**
 * FHIR data type
 */
class CFHIRDataTypeDeviceProperty extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Device.property';

    public ?CFHIRDataTypeCodeableConcept $type = null;

    /** @var CFHIRDataTypeQuantity[] */
    public array $valueQuantity = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    public array $valueCode = [];
}
