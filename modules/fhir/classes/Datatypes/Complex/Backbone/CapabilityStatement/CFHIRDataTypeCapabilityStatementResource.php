<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * FHIR data type
 */
class CFHIRDataTypeCapabilityStatementResource extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'CapabilityStatement.rest.resource';

    /** @var CFHIRDataTypeCode */
    public $type;

    /** @var CFHIRDataTypeCanonical */
    public $profile;

    /** @var CFHIRDataTypeCanonical[] */
    public $supportedProfile;

    /** @var CFHIRDataTypeMarkdown */
    public $documentation;

    /** @var CFHIRDataTypeCapabilityStatementInteraction[] */
    public $interaction;

    /** @var CFHIRDataTypeCode TU */
    public $versioning;

    /** @var CFHIRDataTypeBoolean */
    public $readHistory;

    /** @var CFHIRDataTypeBoolean */
    public $updateCreate;

    /** @var CFHIRDataTypeBoolean */
    public $conditionalCreate;

    /** @var CFHIRDataTypeCode */
    public $conditionalRead;

    /** @var CFHIRDataTypeBoolean */
    public $conditionalUpdate;

    /** @var CFHIRDataTypeCode */
    public $conditionalDelete;

    /** @var CFHIRDataTypeCode[] */
    public $referencePolicy;

    /** @var CFHIRDataTypeString[] */
    public $searchInclude;

    /** @var CFHIRDataTypeString[] */
    public $searchRevInclude;

    /** @var CFHIRDataTypeCapabilityStatementSearchParam[] */
    public $searchParam;

    /** @var CFHIRDataTypeCapabilityStatementOperation[] */
    public $operation;
}
