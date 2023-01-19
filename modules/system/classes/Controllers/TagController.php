<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\NotFoundException;
use Ox\Mediboard\System\CTag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to handle CTag objects
 */
class TagController extends CController
{
    /**
     * @param CTag $tag
     *
     * @return Response
     *
     * @throws ApiException
     *
     * @api
     */
    public function showTag(CTag $tag): Response
    {
        return $this->renderApiResponse(
            new Item($tag)
        );
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws NotFoundException
     * @throws ApiRequestException
     * @throws Exception
     *
     * @api
     */
    public function listTags(RequestApi $request_api): Response
    {
        $tag = new CTag();

        $tags = $tag->loadList(
            $request_api->getFilterAsSQL($tag->getDS()),
            $request_api->getSortAsSql(),
            $request_api->getLimitAsSQL()
        );

        $total = $tag->countListFromRequestApi($request_api);

        $resource = Collection::createFromRequest($request_api, $tags);
        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

        return $this->renderApiResponse(
            $resource
        );
    }
}
