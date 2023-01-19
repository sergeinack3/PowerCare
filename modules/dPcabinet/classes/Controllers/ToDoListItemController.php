<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Cabinet\CToDoListItem;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TodolistitemController
 */
class ToDoListItemController extends CController
{
    public const HANDLED_PARAMETER = 'handled_date';

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws Exception
     * @api
     */
    public function listToDoListItem(RequestApi $request_api): Response
    {
        $handled_date = $request_api->getRequest()->get(self::HANDLED_PARAMETER);

        $todo_item = new CToDoListItem();
        $ds        = $todo_item->getDS();
        $where     = ['todo_list_item.user_id' => $ds->prepare('= ?', CMediusers::get()->_id)];

        if ($handled_date !== null) {
            $where['todo_list_item.handled_date'] = $ds->prepare(
                '= ? OR todo_list_item.handled_date IS NULL',
                $handled_date ?: CMbDT::date()
            );
        }

        $collection = Collection::createFromRequest($request_api, $todo_item->loadList($where));

        return $this->renderApiResponse($collection);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @api
     */
    public function createToDoListItem(RequestApi $request_api): Response
    {
        $collection = $request_api->getModelObjectCollection(CToDoListItem::class);
        $item       = $this->storeCollection($collection);

        return $this->renderApiResponse($item, Response::HTTP_CREATED);
    }

    /**
     * @param CToDoListItem $item
     *
     * @return Response
     * @throws CMbException
     * @api
     */
    public function deleteToDoListItem(CToDoListItem $item): Response
    {
        $this->deleteObject($item);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param RequestApi    $request_api
     * @param CToDoListItem $todo_item
     *
     * @return Response
     * @throws ApiException
     * @throws CMbException
     * @throws RequestContentException
     * @api
     */
    public function updateToDoListItem(RequestApi $request_api, CToDoListItem $todo_item): Response
    {
        /** @var CToDoListItem $todo_item */
        $todo_item = $request_api->getModelObject($todo_item);
        $item      = $this->storeObject($todo_item);

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @param CToDoListItem $todo_item
     * @param RequestApi    $request_api
     *
     * @return Response
     * @throws ApiException
     * @api
     */
    public function getToDoListItem(CToDoListItem $todo_item, RequestApi $request_api): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $todo_item));
    }
}
