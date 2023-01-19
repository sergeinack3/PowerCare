<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInteger;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetExpansionContains extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.expansion.contains';

    public ?CFHIRDataTypeUri $system = null;

    public ?CFHIRDataTypeBoolean $abstract = null;

    public ?CFHIRDataTypeBoolean $inactive = null;

    public ?CFHIRDataTypeString $version = null;

    public ?CFHIRDataTypeCode $code = null;

    public ?CFHIRDataTypeString $display = null;

    public ?CFHIRDataTypeValueSetInConceptDesignation $designation = null;

    public ?CFHIRDataTypeValueSetExpansionContains $contains = null;
}
