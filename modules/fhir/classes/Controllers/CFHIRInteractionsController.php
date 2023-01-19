<?php

/**
 * @package Mediboard\FHIR\Controllers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Controllers;

use Exception;
use Ox\Interop\Fhir\Api\Request\CRequestFHIR;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionHistory;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CFHIRInteractionsController extends CFHIRController
{
    /**
     * Search route
     *
     * @param CRequestFHIR $request Request
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function read(CRequestFHIR $request): Response
    {
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Search route
     *
     * @param CRequestFHIR $request Request
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function history(CRequestFHIR $request): Response
    {
        $version_id  = $request->getResourceVersion();
        $resource    = $request->getResource();

        /** @var CFHIRInteractionHistory $interaction */
        $interaction = $request->getInteraction()
            ->setVersionId($version_id);

        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Capability statement routes
     *
     * @param CRequestFHIR $request
     *
     * @return Response
     * @throws CFHIRException
     * @throws Exception
     * @api
     */
    public function metadata(CRequestFHIR $request): Response
    {
        /** @var CFHIRResourceCapabilityStatement $resource */
        $resource = $request->getResource();
        $resource->setBaseFHIRVersion($resource->getResourceFHIRVersion());

        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Search route
     *
     * @param CRequestFHIR $request Request
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function search(CRequestFHIR $request): Response
    {
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Create resource
     *
     * @param CRequestFHIR $request Request
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function create(CRequestFHIR $request): Response
    {
        $data             = $request->getContent();
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction, $data);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Update resource
     *
     * @param String   $resource    Resource name
     * @param int|null $resource_id Resource ID
     * @param Request  $request     Request
     *
     * @return Response
     * @throws CFHIRException
     * @throws Exception
     * @api
     */
    public function update(CRequestFHIR $request): Response
    {
        $data             = $request->getContent();
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction, $data);

        return $this->renderFHIRResponse($resourceResponse);
    }

    /**
     * Update resource
     *
     * @param String   $resource    Resource name
     * @param int|null $resource_id Resource ID
     * @param Request  $request     Request
     *
     * @return Response
     * @throws CFHIRException
     * @throws Exception
     * @api
     */
    public function delete(CRequestFHIR $request): Response
    {
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }
}
