<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Client\CFHIRClient;
use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Mediboard\Sante400\CIdSante400;
use Psr\SimpleCache\InvalidArgumentException;

class ResourceMarkerMiddleware implements MiddlewareInterface
{
    /** @var string */
    public const OPTION_RETRIEVE_RESOURCES = 'retrieve_resources';

    /**
     * @param Envelope       $envelope
     * @param StackInterface $stack
     *
     * @return CFHIRResource
     * @throws InvalidArgumentException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // Add reference for header Location
        /** @var CFHIRClient $client */
        $client = $envelope->last(CFHIRClient::class);
        $object = $client->getOption('object');
        if (!$object instanceof CStoredObject) {
            return $envelope;
        }

        $interaction = $client->getInteraction();
        if (!$interaction instanceof CFHIRInteractionUpdate && !$interaction instanceof CFHIRInteractionCreate) {
            return $envelope;
        }

        $response = $envelope->getResponse();

        $location_header = $response->getHeader('Location');
        if (is_array($location_header)) {
            $location_header = reset($location_header);
        }

        if (!$location_header) {
            return $envelope;
        }

        $reference            = new CFHIRDataTypeReference();
        $reference->reference = new CFHIRDataTypeString($location_header);
        if ($resource_id = $reference->getReferenceID()) {
            $idex = CIdSante400::getMatch($object->_class, $client->getReceiver()->_tag_fhir, null, $object->_id);
            if (!$idex->_id || $idex->id400 !== $resource_id) {
                $idex->id400 = $resource_id;
                $idex->store();
            }
        }

        return $envelope;
    }
}
