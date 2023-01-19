<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeConceptMapGroup extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ConceptMap.group';

    /** @var CFHIRDataTypeUri */
    public $source;

    /** @var CFHIRDataTypeString */
    public $sourceVersion;

    /** @var CFHIRDataTypeUri */
    public $target;

    /** @var CFHIRDataTypeString */
    public $targetVersion;

    /** @var CFHIRDataTypeConceptMapElement[] */
    public $element;
}
