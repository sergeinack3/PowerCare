<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeConceptMapDependsOn extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ConceptMap.group.element.target.dependsOn';

    /** @var CFHIRDataTypeUri */
    public $property;

    /** @var CFHIRDataTypeCanonical */
    public $system;

    /** @var CFHIRDataTypeString */
    public $value;

    /** @var CFHIRDataTypeString */
    public $display;
}
