<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Throwable;

/**
 * The create interaction creates a new resource in a server-assigned location
 */
class CFHIRInteractionCreate extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "create";

    /** @var string */
    public const METHOD = 'POST';

    /**
     * @param CFHIRResource $resource
     * @param CFHIRParser   $result
     *
     * @return CFHIRResponse
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        // force to set actor and interactions
        $parsed_resource = $resource->buildFrom($result->getResource());

        // integrate data received
        $resource_stored = $resource->handle($parsed_resource);

        // object stored
        $object = $resource_stored ? $resource_stored->getObject() : null;

        try {
            $delegated_mapper = $resource_stored ? $resource_stored->getDelegatedMapper() : null;

            // force to set actor and interactions
            $resource_stored = $resource->buildFrom($resource_stored);

            if ($delegated_mapper && $object) {
                $resource_stored->setMapper($delegated_mapper);
                $resource_stored->mapFrom($object);
            }
        } catch (Throwable $exception) {
        } finally {
            // force id in resource_stored
            if ($object && $object->_id && !$resource_stored->getResourceId()) {
                $resource_stored->setId(new CFHIRDataTypeString($object->getUuid()));
            }
        }

        $this->setResource($resource_stored);

        return new CFHIRResponse($this, $this->format);
    }

    /**
     * Create resource on this with is resource type name
     *
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return $this->resourceType;
    }

    /**
     * @param array|null|string $data
     *
     * @return string|null
     */
    public function getBody($data): ?string
    {
        if (is_string($data) || !$data) {
            return $data ?: null;
        }

        return CMbArray::get($data, 0);
    }
}
