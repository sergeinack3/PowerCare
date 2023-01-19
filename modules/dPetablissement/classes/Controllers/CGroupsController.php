<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\Response;

/**
 * CGroups controller, allow listing groups with read perm
 */
class CGroupsController extends CController
{
    public const WITH_ROLES = 'with_roles';

    public const ROLE_MAIN      = 'is_main';
    public const ROLE_SECONDARY = 'is_secondary';

    public const GROUP_FIELD_NAME = '_name';

    /**
     * @throws ApiException
     * @api
     */
    public function listGroups(RequestApi $request_api): Response
    {
        $groups = CGroups::loadGroups(PERM_READ);
        /** @var Collection $resource */
        $resource = Collection::createFromRequest($request_api, $groups);

        if ($request_api->getRequest()->query->get(self::WITH_ROLES, false)) {
            $this->setRoles($resource);
        }

        return $this->renderApiResponse($resource);
    }

    /**
     * @throws Exception
     * @api
     */
    public function listFunctions(CGroups $group, RequestApi $request_api): Response
    {
        $functions = $group->loadBackRefs(
            'functions',
            $request_api->getSortAsSql(),
            $request_api->getLimitAsSql(),
            null,
            null,
            null,
            null,
            $request_api->getFilterAsSQL($group->getDS())
        );

        /** @var Collection $resource */
        $resource = Collection::createFromRequest($request_api, $functions);
        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), count($functions));

        return $this->renderApiResponse($resource);
    }

    /**
     * @throws ApiException
     * @api
     *
     */
    public function showGroup(CGroups $group, RequestApi $request_api): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $group));
    }

    /**
     * @param CFunctions $function
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException|HttpException
     * @api
     */
    public function showFunction(CFunctions $function, RequestApi $request_api): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $function));
    }

    /**
     * @throws ApiException|CMbException
     * @api
     */
    public function createGroups(RequestApi $request_api): Response
    {
        $groups = $request_api->getModelObjectCollection(
            CGroups::class,
            [RequestFieldsets::QUERY_KEYWORD_ALL],
            [self::GROUP_FIELD_NAME]
        );

        $collection = $this->storeCollection($groups);

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @throws ApiException|CMbException
     * @api
     */
    public function createFunctions(CGroups $group, RequestApi $request_api): Response
    {
        $functions = $request_api->getModelObjectCollection(
            CFunctions::class,
            [RequestFieldsets::QUERY_KEYWORD_ALL]
        );

        /** @var CFunctions $func */
        foreach ($functions as $func) {
            $func->group_id = $group->_id;
        }

        $collection = $this->storeCollection($functions);

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @throws ApiException|CMbException
     * @api
     */
    public function updateGroup(CGroups $group, RequestApi $request_api): Response
    {
        /** @var CGroups $group_from_request */
        $group_from_request = $request_api->getModelObject(
            $group,
            [RequestFieldsets::QUERY_KEYWORD_ALL],
            [self::GROUP_FIELD_NAME]
        );

        $item = $this->storeObject($group_from_request);

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @throws ApiException|CMbException
     * @api
     */
    public function updateFunction(CFunctions $function, RequestApi $request_api): Response
    {
        /** @var CFunctions $function_from_request */
        $function_from_request = $request_api->getModelObject(
            $function,
            [RequestFieldsets::QUERY_KEYWORD_ALL]
        );

        $item = $this->storeObject($function_from_request);

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @throws CMbException
     * @api
     */
    public function deleteGroup(CGroups $group): Response
    {
        $this->deleteObject($group);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws CMbException
     * @api
     */
    public function deleteFunction(CFunctions $function): Response
    {
        $this->deleteObject($function);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Add is_main and is_secondary to groups depending on the calling user.
     * is_main = true : mean the group is the main group for the user
     * is_secondary = true : mean the group is a secondary group for the user
     *
     * @throws ApiException
     * @throws Exception
     */
    private function setRoles(Collection $groups): void
    {
        $current_user        = CMediusers::get();
        $main_function       = $current_user->loadRefFunction();
        $secondary_functions = $current_user->loadRefsSecondaryFunctions();
        $secondary_groups    = array_unique(CMbArray::pluck($secondary_functions, 'group_id'));

        /** @var Item $item */
        foreach ($groups as $item) {
            $group = $item->getDatas();

            $item->addAdditionalDatas([self::ROLE_MAIN => $main_function->group_id === $group->_id]);
            $item->addAdditionalDatas([self::ROLE_SECONDARY => in_array($group->_id, $secondary_groups)]);
        }
    }
}
