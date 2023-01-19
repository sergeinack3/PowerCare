<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources;

use Exception;
use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeId;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeNarrative;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * FHIR generic resource
 */
abstract class CFHIRDomainResource extends CFHIRResource
{
    protected ?CFHIRDataTypeNarrative $text = null;

    /** @var CFHIRDataTypeResource[] */
    protected array $contained = [];

    /** @var CFHIRDataTypeExtension[] */
    protected array $extension = [];

    /** @var CFHIRDataTypeExtension[] */
    protected array $modifierExtension = [];

    /** @var CFHIRDataTypeIdentifier[]|CFHIRDataTypeIdentifier */
    protected $identifier = [];

    /** @var ResourceDomainMappingInterface */
    protected $object_mapping;

    /** @var bool */
    public $_use_contained = false;

    /**
     * @param string|CFHIRResource $resource_or_class
     * @param CStoredObject $object
     *
     * @return CFHIRDataTypeReference
     * @throws InvalidArgumentException|ReflectionException
     */
    public function addReference($resource_or_class, ?CStoredObject $object = null): CFHIRDataTypeReference
    {
        $resource_reference = parent::addReference($resource_or_class, $object);
        if ($this->isContainedActivated()) {
            if ($should_make_mapping = is_string($resource_or_class)) {
                $resource_or_class = new $resource_or_class();
            }
            $contained_resource               = $this->buildFrom($resource_or_class);
            $contained_resource->summary      = true;
            $contained_resource->is_contained = true;

            // mapping only if class is given in parameter
            if ($should_make_mapping) {
                $contained_resource->mapFrom($object);
            }

            // force id for concordance between reference and resource
            $contained_resource->setId(new CFHIRDataTypeId(substr($resource_reference->reference->getValue(), 1)));

            // link reference and resource
            $resource_reference->setTargetResource($contained_resource);

            // add in contained resources
            $this->addContained(new CFHIRDataTypeResource($contained_resource));
        }

        return $resource_reference;
    }

    /**
     * @param CFHIRDataTypeExtension ...$modifierExtension
     *
     * @return CFHIRDomainResource
     */
    public function setModifierExtension(CFHIRDataTypeExtension ...$modifierExtension): CFHIRDomainResource
    {
        $this->modifierExtension = $modifierExtension;

        return $this;
    }

    /**
     * @param CFHIRDataTypeExtension ...$modifierExtension
     *
     * @return CFHIRDomainResource
     */
    public function addModifierExtension(CFHIRDataTypeExtension ...$modifierExtension): CFHIRDomainResource
    {
        $this->modifierExtension = array_merge($this->modifierExtension, $modifierExtension);

        return $this;
    }

    /**
     * @return CFHIRDataTypeExtension[]
     */
    public function getModifierExtension(): array
    {
        return $this->modifierExtension;
    }

    /**
     * @return CFHIRDataTypeExtension[]
     */
    public function mapModifierExtension(): void
    {
        $this->modifierExtension = $this->object_mapping->mapModifierExtension();
    }

    /**
     * @param CFHIRDataTypeNarrative|null $text
     *
     * @return CFHIRDomainResource
     */
    public function setText(?CFHIRDataTypeNarrative $text): CFHIRDomainResource
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return CFHIRDataTypeNarrative|null
     */
    public function getText(): ?CFHIRDataTypeNarrative
    {
        return $this->text;
    }

    /**
     * Map property Text
     */
    public function mapText(): void
    {
        $this->text = $this->object_mapping->mapText();
    }

    public function mapContained(): void
    {
        $this->addContained(...$this->object_mapping->mapContained());
    }

    /**
     * @param CFHIRDataTypeResource ...$contained
     *
     * @return CFHIRDomainResource
     */
    public function setContained(CFHIRDataTypeResource ...$contained): CFHIRDomainResource
    {
        $this->contained = $contained;

        return $this;
    }

    /**
     * @param CFHIRDataTypeResource ...$contained
     *
     * @return CFHIRDomainResource
     */
    public function addContained(CFHIRDataTypeResource ...$contained): CFHIRDomainResource
    {
        $this->contained = array_merge($this->contained, $contained);

        return $this;
    }

    /**
     * @return CFHIRDataTypeResource[]
     */
    public function getContained(): array
    {
        return $this->contained;
    }

    /**
     * @param CFHIRDataTypeExtension ...$extension
     *
     * @return CFHIRDomainResource
     */
    public function setExtension(CFHIRDataTypeExtension ...$extension): CFHIRDomainResource
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @param CFHIRDataTypeExtension ...$extension
     *
     * @return CFHIRDomainResource
     */
    public function addExtension(CFHIRDataTypeExtension ...$extension): CFHIRDomainResource
    {
        $this->extension = array_merge($this->extension, $extension);

        return $this;
    }

    /**
     * @return CFHIRDataTypeExtension[]
     */
    public function getExtension(): array
    {
        return $this->extension;
    }

    /**
     * @return CFHIRDataTypeIdentifier[]|CFHIRDataTypeIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param CFHIRDataTypeIdentifier ...$identifier
     *
     * @return CFHIRDomainResource
     */
    public function setIdentifier(CFHIRDataTypeIdentifier ...$identifier): CFHIRDomainResource
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @param CFHIRDataTypeIdentifier ...$identifier
     *
     * @return CFHIRDomainResource
     */
    public function addIdentifier(CFHIRDataTypeIdentifier ...$identifier): CFHIRDomainResource
    {
        $this->identifier = array_merge($this->identifier, $identifier);

        return $this;
    }

    /**
     * @param CFHIRResource $resource
     * @param CStoredObject        $object
     *
     * @return string
     * @throws Exception
     */
    protected function getResourceIdentifier(CFHIRResource $resource, ?CStoredObject $object): string
    {
        if ($this->isContainedActivated()) {
            $identifier = ($object && $object->_id) ? $this->getInternalId($object) : CMbSecurity::generateUUID();

            return "#" . $identifier;
        }

        return parent::getResourceIdentifier($resource, $object);
    }

    /**
     * @return bool
     */
    protected function isContainedActivated(): bool
    {
        // todo utiliser une config ? un params dans l'url ?
        return $this->_use_contained;
    }

    /**
     * Map property extension
     */
    protected function mapExtension(): void
    {
        $this->extension = $this->object_mapping->mapExtension();
    }

    protected function mapIdentifier(): void
    {
        $this->identifier = $this->object_mapping->mapIdentifier();
    }

    /**
     * @return bool
     */
    public function isSummary(): bool
    {
        return $this->summary;
    }

    /**
     * Search the first resource in contained field which match with the type
     *
     * @param string      $type
     * @param string|null $with_id
     *
     * @return CFHIRResource|null
     */
    public function getContainedOfType(string $type, string $with_id = null): ?CFHIRResource
    {
        foreach ($this->contained as $datatype_resource) {
            $resource = $datatype_resource->getValue();
            if ($resource instanceof $type) {
                if ($with_id) {
                    if ($resource->getResourceId() !== $with_id) {
                        continue;
                    }
                }

                return $resource;
            }
        }

        return null;
    }
}
