<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeXhtml;

/**
 * Human-readable narrative that contains a summary of the resource
 */
class CFHIRDataTypeNarrative extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Narrative';

    /** @var CFHIRDataTypeCode */
    public $status;

    /** @var CFHIRDataTypeXhtml */
    public $div;
}
