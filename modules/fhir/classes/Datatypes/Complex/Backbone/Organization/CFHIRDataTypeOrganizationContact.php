<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Organization;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;

/**
 * FHIR data type
 */
class CFHIRDataTypeOrganizationContact extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Organization.contact';

    /** @var CFHIRDataTypeCodeableConcept */
    public $purpose;

    /** @var CFHIRDataTypeHumanName */
    public $name;

    /** @var CFHIRDataTypeContactPoint[] */
    public $telecom;

    /** @var CFHIRDataTypeAddress */
    public $address;
}
