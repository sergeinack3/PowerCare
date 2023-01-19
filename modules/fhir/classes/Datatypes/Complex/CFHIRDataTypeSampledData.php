<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDecimal;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;

/**
 * FHIR data type
 */
class CFHIRDataTypeSampledData extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'SampledData';

    /** @var CFHIRDataTypeQuantity */
    public $origin;

    /** @var CFHIRDataTypeDecimal */
    public $period;

    /** @var CFHIRDataTypeDecimal */
    public $factor;

    /** @var CFHIRDataTypeDecimal */
    public $lowerLimit;

    /** @var CFHIRDataTypeDecimal */
    public $upperLimit;

    /** @var CFHIRDataTypePositiveInt */
    public $dimensions;

    /** @var CFHIRDataTypeString */
    public $data;
}
