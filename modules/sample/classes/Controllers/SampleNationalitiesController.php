<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CController;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that answer the api request on CSampleNationality
 */
class SampleNationalitiesController extends CController
{
    /**
     * @api
     *
     * @throws ApiRequestException|ApiException
     */
    public function listNationalities(RequestApi $request_api): Response
    {
        $nationality   = new CSampleNationality();
        $nationalities = $nationality->loadListFromRequestApi($request_api);

        /** @var Collection $collection */
        $collection = Collection::createFromRequest($request_api, $nationalities);
        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $nationality->countListFromRequestApi($request_api)
        );

        return $this->renderApiResponse($collection);
    }
}
