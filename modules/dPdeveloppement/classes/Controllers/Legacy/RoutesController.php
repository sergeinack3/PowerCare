<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Exception;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Auth\CAuthentication;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Core\Kernel\Exception\RouteException;
use Ox\Core\Kernel\Routing\RouteManager;
use Ox\Core\Kernel\Routing\RouterBridge;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class RoutesController extends CLegacyController
{
    public function vw_routes(): void
    {
        $this->checkPermAdmin();
        CView::checkin();

        $root_dir      = CAppUI::conf('root_dir');
        $route_manager = new RouteManager();
        $routes        = $route_manager->loadAllRoutes(false)->getRouteCollection();
        $file          = $route_manager->getAllRoutesPath();
        $file_date     = filectime($file);
        $file_size     = CMbString::toDecaBinary(filesize($file));
        $routes_count  = count($routes);
        $ressources    = $route_manager->getRessources();
        $router        = RouterBridge::getInstance();
        $cache_sf_path = $router->getOption('cache_dir');
        $files_sf      = [];

        if ($cache_sf_path) {
            foreach (glob($cache_sf_path . '*.php') as $_file) {
                $files_sf[] = [
                    'path' => $_file,
                    'date' => filectime($_file),
                    'size' => CMbString::toDecaBinary(filesize($_file)),
                ];
            }
        }

        $tpl_vars = [
            'file'         => $file,
            'file_date'    => $file_date,
            'file_size'    => $file_size,
            'routes_count' => $routes_count,
            'ressources'   => $ressources,
            'files_sf'     => $files_sf,
            'root_dir'     => $root_dir,
        ];
        $this->renderSmarty('vw_routes', $tpl_vars);
    }

    public function ajax_details_route(): void
    {
        $this->checkPermAdmin();
        $filter = CView::get('route', 'str');
        CView::checkin();

        /** @var RouteCollection $routes */
        $routes = RouterBridge::getInstance()->getRouteCollection();
        $route  = $routes->get($filter);

        $datas = [
            'Condition'    => $route->getCondition(),
            'Defautls'     => $route->getDefaults(),
            'Host'         => $route->getHost(),
            'Methods'      => $route->getMethods(),
            'Options'      => $route->getOptions(),
            'Path'         => $route->getPath(),
            'Requirements' => $route->getRequirements(),
            'Schemes'      => $route->getSchemes(),
        ];

        $json = json_encode($datas, JSON_PRETTY_PRINT);

        $this->renderSmarty(
            'vw_details_route',
            [
                'json' => $json,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function ajax_list_legacy(): void
    {
        $this->checkPermAdmin();
        $root = $this->getRootDir();

        // Actions
        $legacy_actions = CClassMap::getInstance()->getLegacyActions();
        $count_actions  = 0;
        foreach ($legacy_actions as $module => $actions) {
            $count_actions += count($actions);
        }

        // Scripts
        $glob_scripts  = glob($root . '/modules/*/*.php');
        $count_scripts = count($glob_scripts);
        $scripts       = [];
        foreach ($glob_scripts as $script) {
            $script = str_replace($root . '/modules/', '', $script);
            [$module, $script] = explode('/', $script);
            if (!isset($scripts[$module])) {
                $scripts[$module] = [];
            }
            $scripts[$module][] = $script;
        }

        // dosql
        $glob_dosql    = glob($root . '/modules/*/controllers/*.php');
        $count_dosql   = count($glob_dosql);
        $dosql_scripts = [];
        foreach ($glob_dosql as $dosql) {
            $dosql = str_replace($root . '/modules/', '', $dosql);
            [$module, $ctrl, $dosql] = explode('/', $dosql);
            if (!isset($dosql_scripts[$module])) {
                $dosql_scripts[$module] = [];
            }
            $dosql_scripts[$module][] = $dosql;
        }

        $this->renderSmarty(
            'inc_list_legacy',
            [
                'actions'       => $legacy_actions,
                'count_actions' => $count_actions,
                'scripts'       => $scripts,
                'count_scripts' => $count_scripts,
                'dosql_scripts' => $dosql_scripts,
                'count_dosql'   => $count_dosql,
            ]
        );
    }

    public function ajax_list_routes(): void
    {
        $this->checkPermAdmin();

        $filter = CView::get('filter', 'str');
        $filter = $filter ? str_replace('\\\\', '\\', $filter) : null;
        CView::checkin();

        $routes   = RouterBridge::getInstance()->getRouteCollection();
        $classmap = CClassMap::getInstance();

        $route_display = [];
        foreach ($routes as $route_name => $route) {
            $_controller = $route->getDefault('_controller');
            [$controller, $action] = explode('::', $_controller);
            try {
                $map        = $classmap->getClassMap($controller);
                $short_name = $map->short_name;
                $module     = $map->module;
            } catch (Exception $e) {
                $short_name = $controller;
                $module     = null;
            }

            $path = $route->getPath();

            $method = implode('|', $route->getMethods());

            $match = true;
            if ($filter) {
                $match = false;
                if (strpos($controller, $filter) !== false) {
                    $match = true;
                }
                if (strpos($module, $filter) !== false) {
                    $match = true;
                }
                if (strpos($route_name, $filter) !== false) {
                    $match = true;
                }
            }

            if (!$match) {
                continue;
            }

            $route_display[$route_name] = [
                'controller' => $short_name,
                'action'     => $action,
                'path'       => $path,
                'module'     => $module,
                'method'     => $method,
            ];
        }

        $this->renderSmarty(
            'inc_list_routes',
            [
                'filter'        => $filter,
                'route_display' => $route_display,
            ]
        );
    }


    public function vw_create_route(): void
    {
        $this->checkPermAdmin();

        $body_required = CView::get('body_required', 'bool default|0', true);

        CView::checkin();

        $tpl_vars = [
            'allowed_methods'     => RouteManager::ALLOWED_METHODS,
            'allowed_accept'      => RequestFormats::FORMATS,
            'allowed_accept_body' => RequestFormats::FORMATS_BODY,
            'allowed_permissions' => RouteManager::ALLOWED_PERMISSIONS,
            'body_required'       => $body_required,
        ];

        $this->renderSmarty('vw_create_route', $tpl_vars);
    }

    public function ajax_create_route()
    {
        $this->checkPermAdmin();

        $arguments = [
            'route_name'     => CView::get('route_name', 'str notNull'),
            // TODO $request->getRequest()->request->getAlpha('route_name');
            'path'           => CView::get('path', 'str notNull'),
            'controller'     => CView::get('controller', 'str notNull'),
            'methods'        => CView::get('methods', 'str notNull'),
            'req_names'      => CView::get('requirement_name', 'str'),
            'req_types'      => CView::get('requirement_type', 'str'),
            'description'    => CView::get('description', 'str'),
            'openapi'        => CView::get('openapi', 'bool default|1'),
            'param_names'    => CView::get('parameters_name', 'str'),
            'param_types'    => CView::get('parameters_type', 'str'),
            'accept'         => CView::get('accept', 'str'),
            'permission'     => CView::get(
                'permission',
                'enum list|' . implode('|', RouteManager::ALLOWED_PERMISSIONS) . ' default|none'
            ),
            'body_required'  => CView::get('body_required', 'bool default|1', true),
            'content_type'   => CView::get('content_type', 'str'),
            'response_names' => CView::get('response_name', 'str'),
            'response_descs' => CView::get('response_desc', 'str'),
        ];

        CView::checkin();

        if (!isset($arguments['route_name']) || !$arguments['route_name']) {
            throw new CMbException('dPdeveloppement-Api-Error-msg-Route name is mandatory');
        }

        if (!isset($arguments['path']) || !$arguments['path']) {
            throw new CMbException('dPdeveloppement-Api-Error-msg-Path is mandatory');
        }

        if (!isset($arguments['controller']) || !$arguments['controller']) {
            throw new CMbException('dPdeveloppement-Api-Error-msg-Controller is mandatory');
        }

        if (!isset($arguments['methods']) || !$arguments['methods']) {
            throw new CMbException('dPdeveloppement-Api-Error-msg-Method is mandatory');
        }

        $route_manager = new RouteManager();
        $route         = $route_manager->createRouteFromRequest($arguments);

        try {
            $route_manager->checkRoute($arguments['route_name'], $route, false);
        } catch (RouteException | ControllerException $e) {
            throw new CMbException($e->getMessage());
        }

        $route_array = $route_manager->convertRouteToArray($arguments['route_name'], $route);

        $yaml = Yaml::dump($route_array, 10);

        $yaml = preg_replace("/([\w]+: )'([^']*)'/", '\\1\\2', $yaml);
        echo "<pre>" . CMbString::htmlEntities($yaml) . "</pre>";
    }
}
