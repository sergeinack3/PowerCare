<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class  CFHIRDataTypeCapabilityStatementOperation extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CapabilityStatement.rest.resource.operation';

    /** @var CFHIRDataTypeString */
    public $name;

    /** @var CFHIRDataTypeCanonical*/
    public $definition;

    /** @var CFHIRDataTypeMarkdown */
    public $documentation;
}
