<?php

/**
 * @package Mediboard\Fhir\Controllers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Controllers;

use Exception;
use Ox\Interop\Fhir\Api\Request\CRequestFHIR;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CFHIROperationsController extends CFHIRController
{
    /**
     * Create route
     *
     * @param String  $resource Resource name
     * @param Request $request  Request
     *
     * @return Response
     * @throws CFHIRException
     * @throws Exception
     * @api
     */
    public function ihepix(CRequestFHIR $request): Response
    {
        $resource         = $request->getResource();
        $interaction      = $request->getInteraction();
        $resourceResponse = $resource->process($interaction);

        return $this->renderFHIRResponse($resourceResponse);
    }
}
