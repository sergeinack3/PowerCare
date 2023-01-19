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
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;

/**
 * FHIR data type
 */
class CFHIRDataTypeValueSetExpansionParameter extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'ValueSet.expansion.parameter';

    public ?CFHIRDataTypeString $name = null;

    public ?CFHIRDataTypeChoice $value = null;
}
