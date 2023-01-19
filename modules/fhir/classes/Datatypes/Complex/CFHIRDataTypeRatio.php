<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

/**
 * FHIR data type
 */
class CFHIRDataTypeRatio extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Ratio';

    /** @var CFHIRDataTypeQuantity */
    public $numerator;

    /** @var CFHIRDataTypeQuantity */
    public $denominator;
}
