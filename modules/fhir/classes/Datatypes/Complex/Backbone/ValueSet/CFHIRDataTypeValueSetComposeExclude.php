<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetComposeExclude extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.compose.exclude';

    public ?CFHIRDataTypeUri $system = null;

    public ?CFHIRDataTypeString $version = null;

    /** @var CFHIRDataTypeValueSetExConcept[]|null  */
    public ?array $concept = null;

    /** @var CFHIRDataTypeValueSetComposeExFilter[]|null  */
    public ?array $filter = null;

    /** @var CFHIRDataTypeResource[]|null  */
    public ?array $valueSet = null;
}
