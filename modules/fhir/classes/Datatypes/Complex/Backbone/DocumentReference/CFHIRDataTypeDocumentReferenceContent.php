<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;

/**
 * FHIR data type
 */
class CFHIRDataTypeDocumentReferenceContent extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'DocumentReference.content';

    /** @var CFHIRDataTypeAttachment */
    public $attachment;

    /** @var CFHIRDataTypeCoding */
    public $format;
}
