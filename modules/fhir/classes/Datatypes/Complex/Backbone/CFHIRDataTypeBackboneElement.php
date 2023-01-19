<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone;

use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeComplex;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;

/**
 * FHIR data type
 */
class CFHIRDataTypeBackboneElement extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'BackboneElement';

    /** @var CFHIRDataTypeExtension */
    public $modifierExtension;
}
