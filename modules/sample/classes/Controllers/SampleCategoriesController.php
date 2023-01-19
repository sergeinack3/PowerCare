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
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that answer the api request on CSampleCategory
 */
class SampleCategoriesController extends CController
{
    /**
     * @api
     *
     * @throws ApiRequestException|ApiException
     */
    public function listCategories(RequestApi $request_api): Response
    {
        $category   = new CSampleCategory();
        $categories = $category->loadListFromRequestApi($request_api);

        /** @var Collection $collection */
        $collection = Collection::createFromRequest($request_api, $categories);
        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $category->countListFromRequestApi($request_api)
        );

        return $this->renderApiResponse($collection);
    }
}
