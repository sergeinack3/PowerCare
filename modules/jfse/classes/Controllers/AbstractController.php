<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Mediboard\Jfse\Exceptions\RouterException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class JfseController
 *
 * @package Ox\Mediboard\Jfse
 */
abstract class AbstractController
{
    /**
     * Defines the routes available for the entity
     *
     * The action must be the key, and the value is an array with the following entries :
     *   * method  : The name of the method that will execute the actions and return a Response object
     *   * request : The name fo the method that will validate the request parameters and return a Request object
     *
     * @var array
     */
    protected static $routes;

    /** @var string The route */
    protected $route;

    /**
     * JfseController constructor.
     *
     * @param string $route
     *
     * @throws RouterException
     */
    public function __construct(string $route)
    {
        $this->route = $route;

        if (!array_key_exists($this->route, static::$routes)) {
            throw RouterException::routeNotFound($this->route);
        }
    }

    /**
     * Returns the name of the method to execute
     *
     * @return string
     *
     * @throws RouterException
     */
    public function getMethod(): string
    {
        if (!array_key_exists('method', static::$routes[$this->route])) {
            throw RouterException::methodNotFound("method $this->route");
        }

        $method = static::$routes[$this->route]['method'];

        if (!method_exists($this, $method)) {
            throw RouterException::methodNotFound($method);
        }

        return $method;
    }

    /**
     * Call the function that will validate the request parameters and returns a Request object
     *
     * @return Request
     *
     * @throws RouterException
     */
    public function getRequest(): Request
    {
        if (!array_key_exists('method', static::$routes[$this->route])) {
            throw RouterException::methodNotFound("request $this->route");
        }

        if (!array_key_exists('request', static::$routes[$this->route])) {
            static::$routes[$this->route]['request'] = static::$routes[$this->route]['method'] . 'Request';
        }

        $method = static::$routes[$this->route]['request'];

        if (!method_exists($this, $method)) {
            throw RouterException::methodNotFound($method);
        }

        $request = call_user_func([$this, $method]);

        if (!$request instanceof Request) {
            throw RouterException::invalidRequest($this->route);
        }

        return $request;
    }

    /**
     * Helper to avoid making empty request methods
     *
     * @return Request
     */
    public function emptyRequest(): Request
    {
        CCanDo::checkRead();

        return new Request();
    }

    /**
     * Returns the controller's prefix
     *
     * @return string
     */
    abstract public static function getRoutePrefix(): string;
}
