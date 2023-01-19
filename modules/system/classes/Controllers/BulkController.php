<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestBulk;
use Ox\Core\CController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;


/**
 * Handle bulk api request
 */
class BulkController extends CController
{
    /**
     * @return JsonResponse
     *
     * @throws Exception|ApiException
     * @api
     *
     */
    public function execute(RequestApi $request_api): Response
    {
        if ($request_api->getRequest()->headers->get(RequestBulk::HEADER_SUB_REQUEST)) {
            throw new ApiException('Unauthorized bulk operations on sub request');
        }

        $sub_requests  = (new RequestBulk($request_api))->createRequests();
        $stopOnFailure = $request_api->getRequest()->get('stopOnFailure', false);
        $kernel        = $this->container->get('http_kernel');
        $results       = [];
        foreach ($sub_requests as $request_id => $request) {
            // Must change the RequestApi content because of container and auto wiring keeping the same object
            // for injections.
            $request_api->resetFromRequest($request);

            // Handle request
            /** @var Response $response */
            $response        = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
            $reponse_content = $response->getContent();
            if (
                is_string($reponse_content)
                && is_array(json_decode($reponse_content, true))
                && (json_last_error() === JSON_ERROR_NONE)
            ) {
                $reponse_content = json_decode($response->getContent(), true);
            } else {
                $reponse_content = utf8_encode($reponse_content);
            }

            // Build result
            $results[] = [
                'id'     => $request_id,
                'status' => $response->getStatusCode(),
                'body'   => $reponse_content,
            ];

            // Stop on failure
            if ($stopOnFailure && $response->getStatusCode() >= 400) {
                break;
            }
        }

        return $this->renderJsonResponse($results, 200, [], false);
    }
}
