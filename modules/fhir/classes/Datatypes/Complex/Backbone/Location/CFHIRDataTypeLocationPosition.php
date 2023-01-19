<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDecimal;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeLocationPosition extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Location.position';

    /** @var CFHIRDataTypeDecimal */
    public $longitude;

    /** @var CFHIRDataTypeDecimal */
    public $latitude;

    /** @var CFHIRDataTypeDecimal */
    public $altitude;
}
