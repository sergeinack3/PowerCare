<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Exception\CFHIRExceptionForbidden;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Exception\CFHIRExceptionRequired;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * The read interaction accesses the current contents of a resource
 */
class CFHIRInteractionRead extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "read";

    /** @var string */
    private $version_id;

    /** @var string */
    private $resource_id;

    /**
     * CFHIRInteractionRead constructor.
     *
     * @param string|CFHIRResource $resource
     * @param string|null          $format
     */
    public function __construct($resource = null, ?string $format = '')
    {
        parent::__construct($resource, $format);

        if ($this->resource && ($resource_id = $this->resource->getResourceId())) {
            $this->setResourceId($resource_id);

            $meta_version_id = ($meta = $this->resource->getMeta()) && ($meta->versionId) ? $meta->versionId : null;
            if ($meta_version_id && $meta_version_id->getValue()) {
                $this->setVersionId($meta_version_id->getValue());
            }
        }
    }

    /**
     * @param string $resource_id
     *
     * @return CFHIRInteractionRead
     */
    public function setResourceId(string $resource_id): self
    {
        $this->resource_id = $resource_id;
        if ($this->resource) {
            $this->resource->setId(new CFHIRDataTypeString($resource_id));
        }

        return $this;
    }

    /**
     * @param string $version_id
     *
     * @return CFHIRInteractionRead
     */
    public function setVersionId(string $version_id): self
    {
        $this->version_id = $version_id;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param CStoredObject|null $result
     *
     * @throws CFHIRExceptionNotFound
     * @throws Exception
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        if ($result === null) {
            throw new CFHIRExceptionForbidden(
                "Could not retrieve " . $resource->getResourceType() . " #" . $resource->getResourceId()
            );
        }

        $resource->mapFrom($result);
        $this->setResource($resource);

        return new CFHIRResponse($this, $this->format);
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        // todo Split read et vread ?
        if (!$this->resource_id) {
            $interaction_name = $this::NAME;
            throw new CFHIRExceptionRequired("Element 'resource_id' is missing in interaction '$interaction_name'");
        }

        // Read
        if (!$this->version_id) {
            return $this->resourceType . '/' . $this->resource_id;
        }

        // VRead
        return $this->resourceType . '/' . $this->resource_id . '/_history/' . $this->version_id;
    }
}
