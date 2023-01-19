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
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;

/**
 * FHIR data type
 */
class CFHIRDataTypeAttachment extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Attachment';

    /** @var CFHIRDataTypeCode */
    public $contentType;

    /** @var CFHIRDataTypeCode */
    public $language;

    /** @var CFHIRDataTypeBase64Binary */
    public $data;

    /** @var CFHIRDataTypeUri */
    public $url;

    /** @var CFHIRDataTypeUnsignedInt */
    public $size;

    /** @var CFHIRDataTypeBase64Binary */
    public $hash;

    /** @var CFHIRDataTypeString */
    public $title;

    /** @var CFHIRDataTypeDateTime */
    public $creation;
}
