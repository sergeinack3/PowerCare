<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Binary\Mapper;

use Exception;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\BinaryMappingInterface;
use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBase64Binary;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInvalidValue;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceIdentifierTrait;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Binary\CFHIRResourceBinary;
use Ox\Interop\Fhir\Utilities\Helper\DocumentHelper;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class Binary implements BinaryMappingInterface, DelegatedObjectMapperInterface
{
    use CStoredObjectResourceTrait;
    use CStoredObjectResourceIdentifierTrait;

    /** @var CDocumentItem */
    protected $object;

    /** @var CFHIRResourceBinary */
    protected CFHIRResource $resource;

    /**
     * @param CFHIRResourceBinary $resource
     * @param mixed               $object
     *
     * @inheritDoc
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        if (!$object instanceof CDocumentItem) {
            throw new CFHIRExceptionInvalidValue("The object given for mapping binary is not supported");
        }

        $this->resource = $resource;
        $this->object   = $object;
    }

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return BinaryMappingInterface
     */
    public function getMapping(): ResourceMappingInterface
    {
        return $this;
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceBinary::class];
    }

    /**
     * @inheritDoc
     */
    public function mapContentType(): ?CFHIRDataTypeCode
    {
        $content_type = "application/pdf";
        if ($this->object instanceof CFile) {
            if (!$this->object->file_type) {
                throw new CFHIRExceptionInvalidValue('The document should be have a mime type');
            }

            $content_type = DocumentHelper::getContentType($this->object->file_type);
        }

        return new CFHIRDataTypeCode($content_type);
    }

    /**
     * @inheritDoc
     */
    public function mapData(): ?CFHIRDataTypeBase64Binary
    {
        try {
            $content = null;

            // try to retrieve from content already loaded
            if ($this->object instanceof CFile) {
                $content = $this->object->getContent();
            }

            // try to load content
            if (!$content) {
                if (!$content = $this->object->getBinaryContent(true, false)) {
                    throw new CFHIRExceptionInvalidValue('The document should be have a content');
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return new CFHIRDataTypeBase64Binary($content);
    }

    /**
     * @inheritDoc
     */
    public function mapSecurityContext(): ?CFHIRDataTypeReference
    {
        return null;
    }
}
