<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class  CFHIRDataTypeCapabilityStatementRest extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CapabilityStatement.rest';

    /** @var CFHIRDataTypeCode[] */
    public $mode;

    /** @var CFHIRDataTypeMarkdown */
    public $documentation;

    /** @var CFHIRDataTypeCapabilityStatementSecurity */
    public $security;

    /** @var CFHIRDataTypeCapabilityStatementResource[]*/
    public $resource;

    /** @var CFHIRDataTypeCapabilityStatementInteraction[] */
    public $interaction;

    /** @var CFHIRDataTypeReference[] */
    public $searchParam;

    /** @var CFHIRDataTypeReference[] */
    public $operation;

    /** @var CFHIRDataTypeCanonical[] */
    public $compartment;
}
