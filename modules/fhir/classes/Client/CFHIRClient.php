<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client;

use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Client\Response\Response;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Psr\SimpleCache\InvalidArgumentException;

class CFHIRClient
{
    /** @var CReceiverFHIR */
    private $receiver;

    /** @var CFHIRInteraction */
    private $interaction;

    /** @var array */
    private $options = [];

    public function __construct(CReceiverFHIR $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @param CFHIRInteraction $interaction
     * @param CFHIRResource    $resource
     * @param array            $options
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function request(CFHIRInteraction $interaction, array $options = []): Response
    {
        $this->options = $this->getOptions($options);
        $this->interaction = $interaction;

        // get requested resource
        if (!$resource = $this->interaction->getResource()) {
            throw new CFHIRException('');
        }

        // force data to send
        $data = CMbArray::get($options, 'data', null);

        // verify if resource is supported by receiver
        if (!$this->receiver->getResource($resource->getProfile())) {
            throw new CFHIRExceptionNotSupported(
                sprintf("Interaction '%s' is not supported for the '%s", $interaction::NAME, $resource::RESOURCE_TYPE)
            );
        }

        // keep receiver on resource
        $resource->setInteropActor($this->getReceiver());

        // prepare resource
        if (!$data) {
            // if given object, mapping on resource
            if ($object = $this->options['object'] ?? null) {
                if (!$mapper = $this->receiver->getDelegatedMapper($resource->getProfile())) {
                    throw new CFHIRException('DelegatedObjectMapperInterface-msg-config.none');
                }

                $resource->setMapper($mapper);
                $resource->mapFrom($object);
            }

            $data = CFHIRSerializer::serialize($resource, $interaction->format)->getResourceSerialized();
        }

        $response = $this->receiver->sendEvent(
            $interaction,
            null,
            $data,
            $this->options['headers'] ?? [],
            false,
            false,
            $interaction::METHOD
        );

        return new Response(
            $response->getGuzzleResponse(),
            $response->getExchangeHttp(),
            $this,
            $this->options
        );
    }

    /**
     * @param CFHIRInteraction $interaction
     * @param CFHIRResource    $resource
     *
     * @return bool
     * @throws Exception
     */
    private function isSupported(CFHIRInteraction $interaction, CFHIRResource $resource): bool
    {
        return in_array($interaction::NAME, $this->receiver->getAvailableInteractions($resource));
    }

    /**
     * @param array $override_options
     *
     * @return array
     */
    public function getOptions(array $override_options = []): array
    {
        if (!$options = $this->options) {
            $options = array_replace_recursive($this->getDefaultOptions(), $override_options);
        }

        return $this->options = $options;
    }

    /**
     * @return array
     */
    private function getDefaultOptions(): array
    {
        return [
            'serializer' => [
                'pretty' => false, // bool
            ],
            'object' => null, // null|Object (give to delegatedObject)
            'retrieve_location_header' => false, // bool
            'retrieve_type' => 'class', // 'class'|'fields'
            'retrieve_resources' => 'none', // 'none'|'all'|array<class>|string<field>>
            'headers' => [], //array<headerName, headerValue>
            'mark_object' => false // bool
        ];
    }

    /**
     * @return CFHIRInteraction
     */
    public function getInteraction(): CFHIRInteraction
    {
        return $this->interaction;
    }

    /**
     * @return CReceiverFHIR
     */
    public function getReceiver(): CReceiverFHIR
    {
        return $this->receiver;
    }

    /**
     * @param $headers string[]
     */
    public function setHeaders(array $headers): void
    {
        $this->options['headers'] = $headers;
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    public function getOption(string $name)
    {
        $options = $this->getOptions();

        return CMbArray::getRecursive($options, $name);
    }

}
