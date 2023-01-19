<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers;

use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Entities\CSampleBookmark;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller for CSamplebookmarks.
 */
class SampleBookmarksController extends CController
{
    /**
     * @api
     *
     * List the bookmarks for the current user.
     * A filter for user_id = 'current_user_id' is added.
     */
    public function list(RequestApi $request): Response
    {
        $bookmark = new CSampleBookmark();

        $request->getRequestFilter()
            ->addFilter(new Filter('user_id', RequestFilter::FILTER_EQUAL, CMediusers::get()->_id));

        $collection = Collection::createFromRequest($request, $bookmark->loadListFromRequestApi($request));
        $collection->createLinksPagination(
            $request->getOffset(),
            $request->getLimit(),
            $bookmark->countListFromRequestApi($request)
        );

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     *
     * Add a collection of bookmarks.
     * The field user_id is not in a fieldset which avoid the user setting it.
     */
    public function add(RequestApi $request): Response
    {
        $bookmarks = $request->getModelObjectCollection(CSampleBookmark::class);

        $collection = $this->storeCollection($bookmarks);
        $collection->setModelRelations(CSampleBookmark::RELATION_MOVIE);

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @api
     */
    public function delete(CSampleBookmark $bookmark): Response
    {
        $this->deleteObject($bookmark);

        return $this->renderResponse('', Response::HTTP_NO_CONTENT);
    }
}
