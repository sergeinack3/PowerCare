<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Binary;

use Ox\Interop\Fhir\Contracts\Mapping\R4\BinaryMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceBinaryInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBase64Binary;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Description
 */
class CFHIRResourceBinary extends CFHIRResource implements ResourceBinaryInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Binary';

    /** @var string */
    public const VERSION_NORMATIVE = '4.0';

    protected ?CFHIRDataTypeCode $contentType = null;

    protected ?CFHIRDataTypeReference $securityContext = null;

    protected ?CFHIRDataTypeBase64Binary $data = null;

    /** @var BinaryMappingInterface */
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
                    CFHIRInteractionCreate::NAME,
                ]
            );
    }

    /**
     * @param CFHIRDataTypeCode|null $contentType
     *
     * @return CFHIRResourceBinary
     */
    public function setContentType(?CFHIRDataTypeCode $contentType): CFHIRResourceBinary
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getContentType(): ?CFHIRDataTypeCode
    {
        return $this->contentType;
    }

    /**
     * @param CFHIRDataTypeReference|null $securityContext
     *
     * @return CFHIRResourceBinary
     */
    public function setSecurityContext(?CFHIRDataTypeReference $securityContext): CFHIRResourceBinary
    {
        $this->securityContext = $securityContext;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSecurityContext(): ?CFHIRDataTypeReference
    {
        return $this->securityContext;
    }

    /**
     * @return CFHIRDataTypeBase64Binary|null
     */
    public function getData(): ?CFHIRDataTypeBase64Binary
    {
        return $this->data;
    }

    /**
     * @param CFHIRDataTypeBase64Binary|null $data
     *
     * @return CFHIRResourceBinary
     */
    public function setData(?CFHIRDataTypeBase64Binary $data): CFHIRResourceBinary
    {
        $this->data = $data;

        return $this;
    }

    protected function mapContentType(): void
    {
        $this->contentType = $this->object_mapping->mapContentType();
    }

    protected function mapData(): void
    {
        $this->data = $this->object_mapping->mapData();
    }

    protected function mapSecurityContext(): void
    {
        $this->securityContext = $this->object_mapping->mapSecurityContext();
    }
}
