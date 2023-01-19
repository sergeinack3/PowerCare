<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;

/**
 * FHIR data type
 */
class CFHIRDataTypePractitionerRolerNotAvailable extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'PractitionerRole.notAvailable';

    /** @var CFHIRDataTypeString */
    public $description;

    /** @var CFHIRDataTypePeriod */
    public $during;
}
