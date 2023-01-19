<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CClassMap;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Controllers\AbstractController;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Ox\Mediboard\Jfse\Exceptions\RouterException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JfseRouter
 *
 * @package Ox\Mediboard\Jfse
 */
final class Router
{
    /** @var string|null */
    private $route;
    /** @var AbstractController The controller to call */
    private $controller;

    /**
     * Router constructor.
     *
     * @param string|null $route If not set, the route will be set from $_REQUEST
     *
     * @throws Exception
     */
    public function __construct(string $route = null)
    {
        if (!$route) {
            $route = CView::request('route', 'str');
        }

        $this->route      = $route;
        $this->controller = $this->getController();
    }

    /**
     * Returns the controller corresponding to the route
     *
     * @return AbstractController
     * @throws Exception|JfseException|RouterException
     */
    protected function getController(): AbstractController
    {
        $controller = substr($this->route, 0, strpos($this->route, '/'));
        $method     = substr($this->route, strpos($this->route, '/') + 1);

        $controllers = $this->getMapRouteController();

        if (!array_key_exists($controller, $controllers)) {
            throw RouterException::routeNotFound($controller);
        }

        return new $controllers[$controller]($method);
    }

    /**
     * Get a map of prefixes associated to controllers
     *
     * @return array
     * @throws CouldNotGetCache
     * @throws Exception
     */
    private function getMapRouteController(): array
    {
        try {
            $controllers = self::getControllersListFromCache();
        } catch (CouldNotGetCache | Exception $e) {
            $controllers = null;
        }

        if (!$controllers) {
            $controllers_classes = CClassMap::getInstance()->getClassChildren(AbstractController::class);
            $controllers         = [];
            foreach ($controllers_classes as $_controller) {
                $controllers[$_controller::getRoutePrefix()] = $_controller;
            }

            try {
                self::setControllersListInCache($controllers);
            } catch (CouldNotGetCache | Exception $e) {
            }
        }

        return $controllers;
    }

    /**
     * Handle the request, and returns a response based on the route
     *
     * @param string|null $route If not set, the route will be set from $_REQUEST
     *
     * @return Response
     * @throws Exception
     */
    public static function handle(string $route = null): Response
    {
        Utils::setJsonResponseExpectedFlag(boolval(CView::request('json', 'bool default|0')));

        try {
            $router = new Router($route);

            $resident_uid = CView::request('resident_uid', 'str');
            if ($resident_uid) {
                Utils::setResidentUid($resident_uid);
            }

            $response = $router->dispatch();
        } catch (JfseException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * Call the method of the controller corresponding to the route
     *
     * @return Response
     * @throws Exception
     */
    protected function dispatch(): Response
    {
        $request = $this->controller->getRequest();
        CView::checkin();

        return call_user_func([$this->controller, $this->controller->getMethod()], $request);
    }

    /**
     * @return array|null
     * @throws CouldNotGetCache
     * @throws Exception
     */
    private static function getControllersListFromCache(): ?array
    {
        return LayeredCache::getCache(LayeredCache::INNER_OUTER)->get('Jfse-Router-controllers_list');
    }

    /**
     * @param array $controllers
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws Exception
     */
    private static function setControllersListInCache(array $controllers): void
    {
        LayeredCache::getCache(LayeredCache::INNER_OUTER)->set('Jfse-Router-controllers_list', $controllers);
    }
}
