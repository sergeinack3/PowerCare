<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;

/**
 * FHIR data type
 */
class CFHIRDataTypeCapabilityStatementSecurity extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CapabilityStatement.rest.security';

    /** @var CFHIRDataTypeBoolean */
    public $cors;

    /** @var CFHIRDataTypeCodeableConcept[] */
    public $service;

    /** @var CFHIRDataTypeMarkdown */
    public $description;
}
