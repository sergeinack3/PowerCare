<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypeConceptMapTarget extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ConceptMap.group.element.target';

    /** @var CFHIRDataTypeCode */
    public $code;

    /** @var CFHIRDataTypeString */
    public $display;

    /** @var CFHIRDataTypeCode */
    public $equivalence;

    /** @var CFHIRDataTypeString */
    public $comment;

    /** @var CFHIRDataTypeConceptMapDependsOn[] */
    public $dependsOn;

    /** @var CFHIRDataTypeReference */
    public $product;
}
