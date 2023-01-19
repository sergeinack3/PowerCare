<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetExConcept extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.compose.exclude.concept';

    public ?CFHIRDataTypeCode $code = null;

    public ?CFHIRDataTypeString $display = null;

    /** @var CFHIRDataTypeValueSetExConceptDesignation[]|null  */
    public ?array $designation = null;
}