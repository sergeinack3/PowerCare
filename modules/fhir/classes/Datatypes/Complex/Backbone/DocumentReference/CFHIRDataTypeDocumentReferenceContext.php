<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference;

use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * FHIR data type
 */
class CFHIRDataTypeDocumentReferenceContext extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'DocumentReference.context';

    /** @var CFHIRDataTypeReference[] */
    public $encounter;

    /** @var CFHIRDataTypeCodeableConcept[] */
    public $event;

    /** @var CFHIRDataTypePeriod */
    public $period;

    /** @var CFHIRDataTypeCodeableConcept */
    public $facilityType;

    /** @var CFHIRDataTypeCodeableConcept */
    public $practiceSetting;

    /** @var CFHIRDataTypeReference[] */
    public $sourcePatientInfo;

    /** @var CFHIRDataTypeReference[] */
    public $related;

}
