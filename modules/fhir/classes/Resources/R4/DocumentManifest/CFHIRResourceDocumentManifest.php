<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentManifest;

use Ox\Interop\Fhir\Contracts\Mapping\R4\DocumentManifestMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceDocumentManifestInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentManifest\CFHIRDataTypeDocumentManifestRelated;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Description
 */
class CFHIRResourceDocumentManifest extends CFHIRDomainResource implements ResourceDocumentManifestInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'DocumentManifest';

    /** @var string */
    public const SYSTEM = 'urn:ietf:rfc:3986';

    protected ?CFHIRDataTypeIdentifier $masterIdentifier = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeCodeableConcept $type = null;

    protected ?CFHIRDataTypeReference $subject = null;

    protected ?CFHIRDataTypeDateTime $created = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $author = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $recipient = [];

    protected ?CFHIRDataTypeUri $source = null;

    protected ?CFHIRDataTypeString $description = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $content = [];

    /** @var CFHIRDataTypeDocumentManifestRelated[] */
    protected array $related = [];

    /** @var DocumentManifestMappingInterface */
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
                ]
            );
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param CFHIRDataTypeDocumentManifestRelated ...$related
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setRelated(CFHIRDataTypeDocumentManifestRelated ...$related): CFHIRResourceDocumentManifest
    {
        $this->related = $related;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDocumentManifestRelated ...$related
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function addRelated(CFHIRDataTypeDocumentManifestRelated ...$related): CFHIRResourceDocumentManifest
    {
        $this->related = array_merge($this->related, $related);

        return $this;
    }

    /**
     * @return array
     */
    public function getRelated(): array
    {
        return $this->related;
    }

    /**
     * @param CFHIRDataTypeIdentifier|null $masterIdentifier
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setMasterIdentifier(?CFHIRDataTypeIdentifier $masterIdentifier): CFHIRResourceDocumentManifest
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
     * @return CFHIRResourceDocumentManifest
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceDocumentManifest
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
     * @param CFHIRDataTypeCodeableConcept|null $type
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setType(?CFHIRDataTypeCodeableConcept $type): CFHIRResourceDocumentManifest
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
     * @param CFHIRDataTypeReference|null $subject
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setSubject(?CFHIRDataTypeReference $subject): CFHIRResourceDocumentManifest
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
     * @param CFHIRDataTypeDateTime|null $created
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setCreated(?CFHIRDataTypeDateTime $created): CFHIRResourceDocumentManifest
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getCreated(): ?CFHIRDataTypeDateTime
    {
        return $this->created;
    }

    /**
     * @param CFHIRDataTypeReference ...$author
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setAuthor(CFHIRDataTypeReference ...$author): CFHIRResourceDocumentManifest
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$author
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function addAuthor(CFHIRDataTypeReference ...$author): CFHIRResourceDocumentManifest
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
     * @param CFHIRDataTypeReference ...$recipient
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setRecipient(CFHIRDataTypeReference ...$recipient): CFHIRResourceDocumentManifest
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$recipient
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function addRecipient(CFHIRDataTypeReference ...$recipient): CFHIRResourceDocumentManifest
    {
        $this->recipient = array_merge($this->recipient, $recipient);

        return $this;
    }

    /**
     * @return array
     */
    public function getRecipient(): array
    {
        return $this->recipient;
    }

    /**
     * @param CFHIRDataTypeUri|null $source
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setSource(?CFHIRDataTypeUri $source): CFHIRResourceDocumentManifest
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getSource(): ?CFHIRDataTypeUri
    {
        return $this->source;
    }

    /**
     * @param CFHIRDataTypeString|null $description
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setDescription(?CFHIRDataTypeString $description): CFHIRResourceDocumentManifest
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
     * @param CFHIRDataTypeReference ...$content
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function setContent(CFHIRDataTypeReference ...$content): CFHIRResourceDocumentManifest
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$content
     *
     * @return CFHIRResourceDocumentManifest
     */
    public function addContent(CFHIRDataTypeReference ...$content): CFHIRResourceDocumentManifest
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    /**
     * * Map property masterIdentifier
     */
    protected function mapMasterIdentifier(): void
    {
        $this->masterIdentifier = $this->object_mapping->mapMasterIdentifier();
    }

    /**
     * * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * * Map property subject
     */
    protected function mapSubject(): void
    {
        $this->subject = $this->object_mapping->mapSubject();
    }

    /**
     * * Map property created
     */
    protected function mapCreated(): void
    {
        $this->created = $this->object_mapping->mapCreated();
    }


    /**
     * * Map property author
     */
    protected function mapAuthor(): void
    {
        $this->author = $this->object_mapping->mapAuthor();
    }

    /**
     * * Map property recipient
     */
    protected function mapRecipient(): void
    {
        $this->recipient = $this->object_mapping->mapRecipient();
    }

    /**
     * * Map property source
     */
    protected function mapSource(): void
    {
        $this->source = $this->object_mapping->mapSource();
    }

    /**
     * * Map property description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * * Map property content
     */
    protected function mapContent(): void
    {
        $this->content = $this->object_mapping->mapContent();
    }

    /**
     * * Map property related
     */
    protected function mapRelated(): void
    {
        $this->related = $this->object_mapping->mapRelated();
    }
}
