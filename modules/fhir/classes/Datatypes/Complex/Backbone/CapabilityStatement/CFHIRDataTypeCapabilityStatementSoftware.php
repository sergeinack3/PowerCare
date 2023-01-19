<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeCapabilityStatementSoftware extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CapabilityStatement.software';

    /** @var CFHIRDataTypeString */
    public $name;

    /** @var CFHIRDataTypeString */
    public $version;

    /** @var CFHIRDataTypeDateTime */
    public $releaseDate;
}
