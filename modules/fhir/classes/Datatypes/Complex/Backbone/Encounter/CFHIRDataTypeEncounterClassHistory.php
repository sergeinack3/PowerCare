<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;

/**
 * FHIR data type
 */
class CFHIRDataTypeEncounterClassHistory extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Encounter.classHistory';

    public ?CFHIRDataTypeCoding $class = null;

    public ?CFHIRDataTypePeriod $period = null;
}
