<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;

/**
 * FHIR data type
 */
class CFHIRDataTypeEncounterStatusHistory extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Encounter.statusHistory';

    public ?CFHIRDataTypeCode $status = null;

    public ?CFHIRDataTypePeriod $period = null;
}
