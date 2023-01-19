<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetCompose extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.compose';

    public ?CFHIRDataTypeDate $lockedDate = null;

    public ?CFHIRDataTypeBoolean $inactive = null;

    /** @var CFHIRDataTypeValueSetComposeInclude[]|null  */
    public ?array $include = null;

    /** @var CFHIRDataTypeValueSetComposeExclude[]|null  */
    public ?array $exclude = null;
}
