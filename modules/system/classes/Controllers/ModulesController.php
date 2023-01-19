<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CClassMap;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Module\CModule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Route;

/**
 * Module manipulation for API purpose
 */
class ModulesController extends CController
{
    public const LINK_MODULE_URL      = 'module_url';
    public const LINK_MODULE_TABS_URL = 'tabs';
    public const LINK_LIST_MODULES    = 'modules';

    public const STATE_INSTALLED = 'installed';
    public const STATE_ACTIVE    = 'active';
    public const STATE_VISIBLE   = 'visible';

    public const AVAILABLE_STATES = [
        self::STATE_INSTALLED,
        self::STATE_ACTIVE,
        self::STATE_VISIBLE,
    ];

    /**
     * @throws ApiException
     * @api
     */
    public function showModuleLegacy(string $mod_name, RequestApi $request_api): Response
    {
        $module = CModule::getInstalled($mod_name);
        if ($module === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The module ' . $mod_name . ' is not installed.');
        }
        $module->registerTabs();
        $module->buildUrl();

        $item = Item::createFromRequest($request_api, $module);
        $item->addAdditionalDatas(['tabs' => $module->_tabs]);
        $item->getDatas()->setSelfRouteLegacy();

        return $this->renderApiResponse($item);
    }

    /**
     * @throws ApiException
     * @api
     *
     */
    public function showModule(string $mod_name, RequestApi $request_api): Response
    {
        $module = CModule::getActive($mod_name);
        if ($module === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The module ' . $mod_name . ' is not active.');
        }

        $module->buildUrl();

        $item = Item::createFromRequest($request_api, $module);
        $item->addLinks(
            [
                self::LINK_MODULE_URL      => $module->_url,
                self::LINK_MODULE_TABS_URL => $this->generateUrl('system_modules_tabs_list', ['mod_name' => $mod_name]),
            ]
        );

        return $this->renderApiResponse($item);
    }

    /**
     * @throws ApiException
     * @api
     *
     */
    public function listModules(RequestApi $request_api): Response
    {
        $state = $request_api->getRequest()->get('state', self::STATE_INSTALLED);

        $request_limit = $request_api->getRequestLimit();
        $offset        = $request_limit->isInQuery() ? $request_limit->getOffset() : null;
        $limit         = $request_limit->isInQuery() ? $request_limit->getLimit() : null;

        return $this->renderApiResponse($this->buildModuleListResource($state, $offset, $limit, $request_api));
    }

    private function getModuleList(string $state): array
    {
        switch ($state) {
            case self::STATE_INSTALLED:
                return CModule::getInstalled();
            case self::STATE_ACTIVE:
                return CModule::getActive();
            case self::STATE_VISIBLE:
                return CModule::getVisible();
            default:
                throw new HttpException(
                    Response::HTTP_NOT_FOUND,
                    "State '{$state}' is not in " . implode(', ', self::AVAILABLE_STATES)
                );
        }
    }

    /**
     * @throws ApiException
     */
    private function buildModuleListResource(
        string $state,
        ?int $offset = null,
        ?int $limit = null,
        ?RequestApi $request_api = null
    ): Collection {
        $loaded_modules = $this->getModuleList($state);

        $modules = [];

        // In api mod modules are loaded before locales. Manual load is required
        /** @var CModule $module */
        foreach ($loaded_modules as $module) {
            if ($state === self::STATE_ACTIVE && (!$module->getPerm(PERM_READ) || !$module->mod_ui_active)) {
                continue;
            }

            $module->updateFormFields();
            $module->buildUrl();

            $modules[] = $module;
        }

        $total = count($modules);


        CMbArray::pluckSort($modules, SORT_FLAG_CASE | SORT_NATURAL, '_view', CMbArray::PLUCK_SORT_REMOVE_DIACRITICS);

        if ($pagination = ($offset !== null || $limit !== null)) {
            $modules = array_slice($modules, ($offset) ?? 0, $limit);
        }

        $collection = ($request_api)
            ? Collection::createFromRequest($request_api, $modules)
            : new Collection($modules);

        foreach ($collection as $item) {
            $item->addLinks(
                [
                    self::LINK_MODULE_URL      => $item->getDatas()->_url,
                    self::LINK_MODULE_TABS_URL => $this->generateUrl(
                        'system_modules_tabs_list',
                        ['mod_name' => $item->getDatas()->mod_name],
                    ),
                ]
            );
        }

        if ($pagination) {
            if (!$request_api) {
                // Using the ABSOLUTE_URL will cause the url to be http://localhost/api/modules
                // instead of http://localhost/mediboard/api/modules in non request API mode
                $collection->setRequestUrl($this->getApplicationUrl() . $this->generateUrl('system_modules_list'));
            }

            $collection->createLinksPagination($offset, $limit, $total);
            $collection->addLinks(
                [
                    self::LINK_LIST_MODULES => $this->generateUrl(
                            'system_modules_list'
                        ) . '?state=' . self::STATE_ACTIVE,
                ]
            );
        }

        return $collection;
    }

    /**
     * @todo list only autorized routes
     * @param string $mod_name
     *
     * @return Response
     * @throws ApiException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @api
     */
    public function listModuleRoutes(string $mod_name): Response
    {
        $router           = $this->container->get('router');
        $route_collection = $router->getRouteCollection();
        $namespace_prefix = CClassMap::getInstance()->getNamespaceFromModule($mod_name);
        if (!$namespace_prefix) {
            throw new HttpException(404, 'Invalid module name ' . $mod_name);
        }

        $datas = [];
        /**
         * @var string $route_name
         * @var Route  $route
         */
        foreach ($route_collection as $route_name => $route) {
            $controller = $route->getDefault('_controller');
            if (!$namespace_prefix || !str_starts_with($controller, $namespace_prefix)) {
                continue;
            }

            $datas[] = [
                '_type'   => 'path_map',
                '_id'     => $route_name,
                'path'    => $route->getPath(),
                'methods' => $route->getMethods(),
            ];
        }

        $collection = new Collection($datas);
        /** @var Item $item */
        foreach ($collection as $item) {
            $array = $item->getData('methods');
            $item->addLinks([
                                'schema' => $router->getGenerator()->generate(
                                    'system_shemas_routes',
                                    [
                                        'path'   => str_replace('=', '', base64_encode($item->getData('path'))),
                                        'method' => strtolower(reset($array)), // todo forge all methods links
                                    ],
                                    UrlGenerator::ABSOLUTE_URL
                                ),
                            ]
            );
        }

        return $this->renderApiResponse($collection);
    }
}
