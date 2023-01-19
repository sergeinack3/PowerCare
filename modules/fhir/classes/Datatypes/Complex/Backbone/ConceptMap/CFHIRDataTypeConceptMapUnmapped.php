<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeConceptMapUnmapped extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ConceptMap.unmapped';

    /** @var CFHIRDataTypeCode */
    public $mode;

    /** @var CFHIRDataTypeCode */
    public $code;

    /** @var CFHIRDataTypeString */
    public $display;

    /** @var CFHIRDataTypeCanonical */
    public $url;
}
