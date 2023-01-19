<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDecimal;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;

/**
 * Each resource contains an element "meta", of type "Meta", which is a set of metadata that provides technical and
 * workflow context to the resource
 */
class CFHIRDataTypeQuantity extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Quantity';

    /** @var CFHIRDataTypeDecimal */
    public $value;

    /** @var CFHIRDataTypeCode */
    public $comparator;

    /** @var CFHIRDataTypeString */
    public $unit;

    /** @var CFHIRDataTypeUri */
    public $system;

    /** @var CFHIRDataTypeCode */
    public $code;
}
