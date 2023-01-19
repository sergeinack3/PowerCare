<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentReference;

use Ox\Interop\Fhir\Contracts\Mapping\R4\DocumentReferenceMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceDocumentReferenceInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContext;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceRelatesTo;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterReference;

/**
 * FIHR document reference resource
 */
class CFHIRResourceDocumentReference extends CFHIRDomainResource implements ResourceDocumentReferenceInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'DocumentReference';

    /** @var string */
    public const SYSTEM = 'urn:ietf:rfc:3986';

    // attributes
    protected ?CFHIRDataTypeIdentifier $masterIdentifier = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeCode $docStatus = null;

    protected ?CFHIRDataTypeCodeableConcept $type = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $category = [];

    protected ?CFHIRDataTypeReference $subject = null;

    protected ?CFHIRDataTypeInstant $date = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $author = [];

    protected ?CFHIRDataTypeReference $authenticator = null;

    protected ?CFHIRDataTypeReference $custodian = null;

    /** @var CFHIRDataTypeDocumentReferenceRelatesTo[] */
    protected array $relatesTo = [];

    protected ?CFHIRDataTypeString $description = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $securityLabel = [];

    /** @var CFHIRDataTypeDocumentReferenceContent[] */
    protected array $content = [];

    protected ?CFHIRDataTypeDocumentReferenceContext $context = null;

    /** @var DocumentReferenceMappingInterface */
    protected $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionDelete::NAME,
                    CFHIRInteractionUpdate::NAME,
                ]
            )
            ->addSearchAttributes(
                [
                    new SearchParameterReference('encounter'),
                    new SearchParameterReference('patient'),
                ]
            );
    }

    /**
     * @param CFHIRDataTypeIdentifier|null $masterIdentifier
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setMasterIdentifier(?CFHIRDataTypeIdentifier $masterIdentifier): CFHIRResourceDocumentReference
    {
        $this->masterIdentifier = $masterIdentifier;

        return $this;
    }

    /**
     * @return CFHIRDataTypeIdentifier|null
     */
    public function getMasterIdentifier(): ?CFHIRDataTypeIdentifier
    {
        return $this->masterIdentifier;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceDocumentReference
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getStatus(): ?CFHIRDataTypeCode
    {
        return $this->status;
    }

    /**
     * @param CFHIRDataTypeCode|null $docStatus
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setDocStatus(?CFHIRDataTypeCode $docStatus): CFHIRResourceDocumentReference
    {
        $this->docStatus = $docStatus;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getDocStatus(): ?CFHIRDataTypeCode
    {
        return $this->docStatus;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $type
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setType(?CFHIRDataTypeCodeableConcept $type): CFHIRResourceDocumentReference
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getType(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->type;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$category
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setCategory(CFHIRDataTypeCodeableConcept ...$category): CFHIRResourceDocumentReference
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$category
     *
     * @return CFHIRResourceDocumentReference
     */
    public function addCategory(CFHIRDataTypeCodeableConcept ...$category): CFHIRResourceDocumentReference
    {
        $this->category = array_merge($this->category, $category);

        return $this;
    }

    /**
     * @return array
     */
    public function getCategory(): array
    {
        return $this->category;
    }

    /**
     * @param CFHIRDataTypeReference|null $subject
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setSubject(?CFHIRDataTypeReference $subject): CFHIRResourceDocumentReference
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSubject(): ?CFHIRDataTypeReference
    {
        return $this->subject;
    }

    /**
     * @param CFHIRDataTypeInstant|null $date
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setDate(?CFHIRDataTypeInstant $date): CFHIRResourceDocumentReference
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function getDate(): ?CFHIRDataTypeInstant
    {
        return $this->date;
    }

    /**
     * @param CFHIRDataTypeReference ...$author
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setAuthor(CFHIRDataTypeReference ...$author): CFHIRResourceDocumentReference
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$author
     *
     * @return CFHIRResourceDocumentReference
     */
    public function addAuthor(CFHIRDataTypeReference ...$author): CFHIRResourceDocumentReference
    {
        $this->author = array_merge($this->author, $author);

        return $this;
    }

    /**
     * @return array
     */
    public function getAuthor(): array
    {
        return $this->author;
    }

    /**
     * @param CFHIRDataTypeReference|null $authenticator
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setAuthenticator(?CFHIRDataTypeReference $authenticator): CFHIRResourceDocumentReference
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getAuthenticator(): ?CFHIRDataTypeReference
    {
        return $this->authenticator;
    }

    /**
     * @param CFHIRDataTypeReference|null $custodian
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setCustodian(?CFHIRDataTypeReference $custodian): CFHIRResourceDocumentReference
    {
        $this->custodian = $custodian;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getCustodian(): ?CFHIRDataTypeReference
    {
        return $this->custodian;
    }

    /**
     * @param CFHIRDataTypeDocumentReferenceRelatesTo ...$relatesTo
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setRelatesTo(CFHIRDataTypeDocumentReferenceRelatesTo ...$relatesTo): CFHIRResourceDocumentReference
    {
        $this->relatesTo = $relatesTo;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDocumentReferenceRelatesTo ...$relatesTo
     *
     * @return CFHIRResourceDocumentReference
     */
    public function addRelatesTo(CFHIRDataTypeDocumentReferenceRelatesTo ...$relatesTo): CFHIRResourceDocumentReference
    {
        $this->relatesTo = array_merge($this->relatesTo, $relatesTo);

        return $this;
    }

    /**
     * @return CFHIRDataTypeDocumentReferenceRelatesTo[]
     */
    public function getRelatesTo(): array
    {
        return $this->relatesTo;
    }

    /**
     * @param CFHIRDataTypeString|null $description
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setDescription(?CFHIRDataTypeString $description): CFHIRResourceDocumentReference
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getDescription(): ?CFHIRDataTypeString
    {
        return $this->description;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$securityLabel
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setSecurityLabel(CFHIRDataTypeCodeableConcept ...$securityLabel): CFHIRResourceDocumentReference
    {
        $this->securityLabel = $securityLabel;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$securityLabel
     *
     * @return CFHIRResourceDocumentReference
     */
    public function addSecurityLabel(CFHIRDataTypeCodeableConcept ...$securityLabel): CFHIRResourceDocumentReference
    {
        $this->securityLabel = array_merge($this->securityLabel, $securityLabel);

        return $this;
    }

    /**
     * @return array
     */
    public function getSecurityLabel(): array
    {
        return $this->securityLabel;
    }

    /**
     * @param CFHIRDataTypeDocumentReferenceContent ...$content
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setContent(CFHIRDataTypeDocumentReferenceContent ...$content): CFHIRResourceDocumentReference
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDocumentReferenceContent ...$content
     *
     * @return CFHIRResourceDocumentReference
     */
    public function addContent(CFHIRDataTypeDocumentReferenceContent ...$content): CFHIRResourceDocumentReference
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    /**
     * @return CFHIRDataTypeDocumentReferenceContent[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param CFHIRDataTypeDocumentReferenceContext|null $context
     *
     * @return CFHIRResourceDocumentReference
     */
    public function setContext(?CFHIRDataTypeDocumentReferenceContext $context): CFHIRResourceDocumentReference
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDocumentReferenceContext|null
     */
    public function getContext(): ?CFHIRDataTypeDocumentReferenceContext
    {
        return $this->context;
    }

    protected function mapMasterIdentifier(): void
    {
        $this->masterIdentifier = $this->object_mapping->mapMasterIdentifier();
    }

    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    protected function mapCategory(): void
    {
        $this->category = $this->object_mapping->mapCategory();
    }

    protected function mapSubject(): void
    {
        $this->subject = $this->object_mapping->mapSubject();
    }

    protected function mapDate(): void
    {
        $this->date = $this->object_mapping->mapDate();
    }

    protected function mapAuthor(): void
    {
        $this->author = $this->object_mapping->mapAuthor();
    }

    protected function mapRelatesTo(): void
    {
        $this->relatesTo = $this->object_mapping->mapRelatesTo();
    }

    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    protected function mapSecurityLabel(): void
    {
        $this->securityLabel = $this->object_mapping->mapSecurityLabel();
    }

    protected function mapContent(): void
    {
        $this->content = $this->object_mapping->mapContent();
    }

    protected function mapContext(): void
    {
        $this->context = $this->object_mapping->mapContext();
    }

    protected function mapAuthenticator(): void
    {
        $this->authenticator = $this->object_mapping->mapAuthenticator();
    }

    protected function mapCustodian(): void
    {
        $this->custodian = $this->object_mapping->mapCustodian();
    }

    protected function mapDocStatus(): void
    {
        $this->docStatus = $this->object_mapping->mapDocStatus();
    }
}
