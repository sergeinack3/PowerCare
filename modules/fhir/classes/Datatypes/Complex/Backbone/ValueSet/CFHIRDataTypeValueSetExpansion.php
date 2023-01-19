<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInteger;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetExpansion extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.expansion';

    public ?CFHIRDataTypeUri $identifier = null;

    public ?CFHIRDataTypeDateTime $timestamp = null;

    public ?CFHIRDataTypeInteger $total = null;

    public ?CFHIRDataTypeInteger $offset = null;

    /** @var CFHIRDataTypeValueSetExpansionParameter[]|null */
    public ?array $parameter = null;

    /** @var CFHIRDataTypeValueSetExpansionContains[]|null  */
    public ?array $contains = null;
}
