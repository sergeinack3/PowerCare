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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetExConceptDesignation extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.compose.exclude.concept.designation';

    public ?CFHIRDataTypeCode $language = null;

    public ?CFHIRDataTypeCoding $use = null;

    public ?CFHIRDataTypeString $value = null;
}
