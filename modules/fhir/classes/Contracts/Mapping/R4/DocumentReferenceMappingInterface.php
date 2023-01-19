<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContext;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceRelatesTo;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface DocumentReferenceMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'DocumentReference';

    /**
     * @return CFHIRDataTypeIdentifier|null
     */
    public function mapMasterIdentifier(): ?CFHIRDataTypeIdentifier;

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function mapDocStatus(): ?CFHIRDataTypeCode;

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapCategory(): array;

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function mapSubject(): ?CFHIRDataTypeReference;

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function mapDate(): ?CFHIRDataTypeInstant;

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function mapAuthor(): array;

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function mapAuthenticator(): ?CFHIRDataTypeReference;

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function mapCustodian(): ?CFHIRDataTypeReference;

    /**
     * @return CFHIRDataTypeDocumentReferenceRelatesTo[]
     */
    public function mapRelatesTo(): array;

    /**
     * @return CFHIRDataTypeString|null
     */
    public function mapDescription(): ?CFHIRDataTypeString;

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapSecurityLabel(): array;

    /**
     * @return CFHIRDataTypeDocumentReferenceContent[]
     */
    public function mapContent(): array;

    /**
     * @return CFHIRDataTypeDocumentReferenceContext|null
     */
    public function mapContext(): ?CFHIRDataTypeDocumentReferenceContext;
}
