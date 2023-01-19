<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Bundle;

use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Contracts\Mapping\R4\BundleMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceBundleInterface;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleEntry;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleLink;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeSignature;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FIHR patient resource
 */
class CFHIRResourceBundle extends CFHIRResource implements ResourceBundleInterface
{
    /** @var string */
    public const TYPE_BATCH = "batch";
    /** @var string */
    public const TYPE_TRANSACTION = "transaction";
    /** @var string */
    public const TYPE_DOCUMENT = "document";
    /** @var string */
    public const TYPE_COLLECTION = "collection";
    /** @var string */
    public const TYPE_SEARCHSET = "searchset";
    /** @var string */
    public const TYPE_HISTORY = "history";
    /** @var string */
    public const TYPE_BATCH_RESPONSE = "batch-response";
    /** @var string */
    public const TYPE_TRANSACTION_RESPONSE = "transaction-response";
    /** @var string */
    public const TYPE_MESSAGE = "message";

    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Bundle';

    /** @var string */
    public const VERSION_NORMATIVE = '4.0';

    // attributes
    protected ?CFHIRDataTypeIdentifier $identifier = null;

    protected ?CFHIRDataTypeCode $type = null;

    protected ?CFHIRDataTypeInstant $timestamp = null;

    protected ?CFHIRDataTypeUnsignedInt $total = null;

    /** @var CFHIRDataTypeBundleLink[] */
    protected array $link = [];

    /** @var CFHIRDataTypeBundleEntry[] */
    protected array $entry = [];

    protected ?CFHIRDataTypeSignature $signature = null;

    /** @var BundleMappingInterface */
    protected $object_mapping;

    /**
     * @return CFHIRDataTypeIdentifier|null
     */
    public function getIdentifier(): ?CFHIRDataTypeIdentifier
    {
        return $this->identifier;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getType(): ?CFHIRDataTypeCode
    {
        return $this->type;
    }

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function getTimestamp(): ?CFHIRDataTypeInstant
    {
        return $this->timestamp;
    }

    /**
     * @return CFHIRDataTypeUnsignedInt|null
     */
    public function getTotal(): ?CFHIRDataTypeUnsignedInt
    {
        return $this->total;
    }

    /**
     * @return CFHIRDataTypeBundleLink[]
     */
    public function getLink(): array
    {
        return $this->link ?? [];
    }

    /**
     * @return CFHIRDataTypeBundleEntry[]
     */
    public function getEntry(): array
    {
        return $this->entry ?? [];
    }

    /**
     * @return CFHIRDataTypeSignature|null
     */
    public function getSignature(): ?CFHIRDataTypeSignature
    {
        return $this->signature;
    }

    /**
     * @param CFHIRDataTypeIdentifier|null $identifier
     *
     * @return CFHIRResourceBundle
     */
    public function setIdentifier(?CFHIRDataTypeIdentifier $identifier): CFHIRResourceBundle
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode $type
     *
     * @return CFHIRResourceBundle
     */
    public function setType(?CFHIRDataTypeCode $type): CFHIRResourceBundle
    {
        $available_types = [
            self::TYPE_BATCH,
            self::TYPE_BATCH_RESPONSE,
            self::TYPE_COLLECTION,
            self::TYPE_DOCUMENT,
            self::TYPE_HISTORY,
            self::TYPE_MESSAGE,
            self::TYPE_SEARCHSET,
            self::TYPE_TRANSACTION,
            self::TYPE_TRANSACTION_RESPONSE,
        ];

        if ($type) {
            $value_type = $type->getValue();
            if ($value_type && !in_array($value_type, $available_types, true)) {
                throw new CFHIRExceptionNotSupported(
                    "This type : $value_type is not supported type for Bundle resource"
                );
            }
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @param CFHIRDataTypeInstant|null $timestamp
     *
     * @return CFHIRResourceBundle
     */
    public function setTimestamp(?CFHIRDataTypeInstant $timestamp): CFHIRResourceBundle
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUnsignedInt|null $total
     *
     * @return CFHIRResourceBundle
     */
    public function setTotal(?CFHIRDataTypeUnsignedInt $total): CFHIRResourceBundle
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBundleLink ...$links
     *
     * @return CFHIRResourceBundle
     */
    public function setLink(CFHIRDataTypeBundleLink ...$links): CFHIRResourceBundle
    {
        $this->link = $links;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBundleLink|null $link
     *
     * @return self
     */
    public function addLink(CFHIRDataTypeBundleLink ...$links): self
    {
        $this->link = array_merge($this->link ?? [], $links);

        return $this;
    }

    /**
     * @param CFHIRDataTypeBundleEntry ...$entry
     *
     * @return self
     */
    public function addEntry(CFHIRDataTypeBundleEntry ...$entry): self
    {
        $this->entry = array_merge($this->entry ?? [], $entry);

        return $this;
    }

    /**
     * @param CFHIRDataTypeBundleEntry ...$entries
     *
     * @return CFHIRResourceBundle
     */
    public function setEntry(CFHIRDataTypeBundleEntry ...$entries): CFHIRResourceBundle
    {
        $this->entry = $entries;

        return $this;
    }

    /**
     * @param CFHIRDataTypeSignature|null $signature
     *
     * @return CFHIRResourceBundle
     */
    public function setSignature(?CFHIRDataTypeSignature $signature): CFHIRResourceBundle
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return CCapabilitiesResource
     */
    protected function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionUpdate::NAME,
                ]
            );
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return $this
     */
    public function addResource(CFHIRResource $resource): self
    {
        $entry = new CFHIRDataTypeBundleEntry();

        if ($resource_id = $resource->getResourceId()) {
            $fullUrl = CFHIRController::getUrl(
                "fhir_read",
                [
                    'resource'    => $resource->getResourceType(),
                    'resource_id' => $resource_id,
                ]
            );

            $entry->setFullUrl($fullUrl);
        }

        $entry->setResourceElement(new CFHIRDataTypeResource($resource));

        return $this->addEntry($entry);
    }

    /**
     * @param CStoredObject $object
     *
     * @return string
     */
    protected function getInternalId(?CStoredObject $object = null): string
    {
        return CMbSecurity::generateUUID();
    }

    /**
     * * Map property identifier
     */
    protected function mapIdentifier(): void
    {
        $this->identifier = $this->object_mapping->mapIdentifier();
    }

    /**
     * * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * * Map property timestamp
     */
    protected function mapTimestamp(): void
    {
        $this->timestamp = $this->object_mapping->mapTimestamp();
    }

    /**
     * * Map property total
     */
    protected function mapTotal(): void
    {
        $this->total = $this->object_mapping->mapTotal();
    }

    /**
     * * Map property link
     */
    protected function mapLink(): void
    {
        $this->link = $this->object_mapping->mapLink();
    }

    /**
     * * Map property entry
     */
    protected function mapEntry(): void
    {
        $this->entry = $this->object_mapping->mapEntry();
    }

    /**
     * * Map property signature
     */
    protected function mapSignature(): void
    {
        $this->signature = $this->object_mapping->mapSignature();
    }
}
