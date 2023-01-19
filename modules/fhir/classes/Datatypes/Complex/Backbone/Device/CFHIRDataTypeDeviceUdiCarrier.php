<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBase64Binary;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeDeviceUdiCarrier extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Device.udiCarrier';

    public ?CFHIRDataTypeString $deviceIdentifier = null;

    public ?CFHIRDataTypeUri $issuer = null;

    public ?CFHIRDataTypeUri $jurisdiction = null;

    public ?CFHIRDataTypeBase64Binary $carrierAIDC = null;

    public ?CFHIRDataTypeString $carrierHRF = null;

    public ?CFHIRDataTypeCode $entryType = null;
}
