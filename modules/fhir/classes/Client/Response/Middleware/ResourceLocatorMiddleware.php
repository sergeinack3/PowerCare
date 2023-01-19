<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Client\CFHIRClient;
use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Psr\SimpleCache\InvalidArgumentException;

class ResourceLocatorMiddleware implements MiddlewareInterface
{
    /** @var CFHIRClient */
    private $client;

    /** @var string|string[] */
    private $resource_retriever;
    /** @var string */
    private $type_retriever;

    /**
     * @param Envelope       $envelope
     * @param StackInterface $stack
     *
     * @return Envelope
     * @throws InvalidArgumentException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // Add reference for header Location
        /** @var CFHIRClient $client */
        $this->client    = $envelope->last(CFHIRClient::class);
        $interaction     = $this->client->getInteraction();
        $option_location = $this->getOption('retrieve_location_header');
        if ($option_location && ($interaction instanceof CFHIRInteractionUpdate || $interaction instanceof CFHIRInteractionCreate)) {
            $response = $envelope->getResponse();
            if ($location_header = $response->getHeader('Location')) {
                $reference = new CFHIRDataTypeReference($location_header);
                $envelope  = $envelope->with($reference);
            }
        }

        // search references in resource
        if ($this->supportRetrieveResources($envelope) && $envelope->getResource()) {
            $references = $this->searchReferences($envelope);

            foreach ($references as $reference) {
                $envelope = $envelope->with($reference);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    private function getOption(string $name)
    {
        $options = $this->client->getOptions();

        return CMbArray::getRecursive($options, $name);
    }

    private function resolveTypeRetrieve(Envelope $envelope): void
    {
        $type               = $this->getOption('retrieve_type');
        $resource_retriever = $this->getOption('retrieve_resources') ?: 'none';
        $authorize_types    = ['fields', 'class'];
        $is_type_incorrect  = !$type || !is_string($type) || !in_array($type, $authorize_types);
        if (!($resource = $envelope->getResource()) || $is_type_incorrect || $resource_retriever === 'none') {
            $this->resource_retriever = 'none';
            $this->type_retriever     = 'class';

            return;
        }

        $this->type_retriever     = $type;
        $this->resource_retriever = $resource_retriever;
        if (is_string($resource_retriever) && $this->resource_retriever === 'all') {
            return;
        }

        if (!is_array($this->resource_retriever)) {
            $this->resource_retriever = [$this->resource_retriever];
        }

        if ($type === 'class') {
            $function_filter = function ($class) {
                return is_subclass_of($class, CFHIRResource::class);
            };
        } else {
            $fields          = CFHIRDefinition::getFields($resource);
            $function_filter = function ($field) use ($fields, $type, $resource) {
                if (in_array($field, $fields)) {
                    /** @var CFHIRDataType $datatype */
                    $datatype = $resource->{$field};
                    if ($datatype && $datatype instanceof CFHIRDataTypeReference && !$datatype->isNull()) {
                        return true;
                    }
                }

                return false;
            };
        }

        $resource_retriever = array_filter($this->resource_retriever, $function_filter);

        $this->resource_retriever = count($resource_retriever) > 0 ? $resource_retriever : 'none';
    }

    /**
     * @param Envelope $envelope
     *
     * @return bool
     */
    private function supportRetrieveResources(Envelope $envelope): bool
    {
        $this->resolveTypeRetrieve($envelope);

        return $this->resource_retriever && $this->resource_retriever !== 'none';
    }

    /**
     * @param Envelope $envelope
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function searchReferences(Envelope $envelope): array
    {
        $resource = $envelope->getResource();
        $map      = new FHIRClassMap();

        $references = [];
        foreach ($this->getReferenceFields($resource) as $reference_field) {
            /** @var CFHIRDataTypeReference[]|CFHIRDataTypeReference $fields */
            if (!property_exists($resource, $reference_field) || !($fields = $resource->{$reference_field})) {
                continue;
            }

            if (!is_array($fields)) {
                $fields = [$fields];
            }

            foreach ($fields as $field) {
                if ($field->isNull()) {
                    continue;
                }

                // find target resource
                if (!$target_resource = $field->resolveResourceTarget()) {
                    continue;
                }

                if ($this->resource_retriever !== 'all') {
                    // filter per type
                    $target_resources_class = array_map(
                        'get_class',
                        $map->resource->listResources($target_resource::RESOURCE_TYPE)
                    );
                    if (count(array_diff($target_resources_class, $this->resource_retriever)) !== 1) {
                        continue;
                    }
                }

                if ($field->resolveUrl()) {
                    $references[] = $field;
                }
            }
        }

        return $references;
    }


    /**
     * @param CFHIRResource $resource
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function getReferenceFields(CFHIRResource $resource): array
    {
        $fields = [];

        $search_fields = $this->type_retriever === 'class'
            ? CFHIRDefinition::getFields($resource)
            : $this->resource_retriever;
        foreach ($search_fields as $field) {
            $def_element = CFHIRDefinition::getElementDefinition($resource, $field);
            $datatype    = CMbArray::getRecursive($def_element, 'datatype class');
            if (!$datatype || $datatype !== CFHIRDataTypeReference::class) {
                continue;
            }

            $fields[] = $field;
        }

        return $fields;
    }

}
