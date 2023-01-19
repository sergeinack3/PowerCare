<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBase64Binary;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;

/**
 * FHIR data type
 */
class CFHIRDataTypeSignature extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Signature';

    /** @var CFHIRDataTypeCoding[] */
    public $type;

    /** @var CFHIRDataTypeInstant */
    public $when;

    /** @var CFHIRDataTypeReference */
    public $who;

    /** @var CFHIRDataTypeReference */
    public $onBehalfOf;

    /** @var CFHIRDataTypeCode */
    public $targetFormat;

    /** @var CFHIRDataTypeCode */
    public $sigFormat;

    /** @var CFHIRDataTypeBase64Binary */
    public $data;
}
