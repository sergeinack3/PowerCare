<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

/**
 * Class RouterException
 *
 * @package Ox\Mediboard\Jfse\Exceptions
 */
final class RouterException extends JfseException
{
    /**
     * @param string $route [optional] The name of the route
     *
     * @return RouterException
     */
    public static function routeNotFound(string $route = null): self
    {
        return new static('RouteNotFound', 'JfseRouterException-error-route_not_found', [$route]);
    }

    /**
     * @param string $route [optional] The name of the route
     *
     * @return RouterException
     */
    public static function methodNotFound(string $route = null): self
    {
        return new static('MethodNotFound', 'JfseRouterException-error-method_not_found', [$route]);
    }

    /**
     * @param string $route [optional] The name of the route
     *
     * @return RouterException
     */
    public static function invalidRequest(string $route = null): self
    {
        return new static('InvalidRequest', 'JfseRouterException-error-invalid_request', [$route]);
    }

    /**
     * @param string $variable [optional] The name of the variable
     *
     * @return RouterException
     */
    public static function smartyVariableNotFound(string $variable = null): self
    {
        return new static('SmartyVariableNotFound', 'JfseRouterException-error-smarty_variable_not_found', [$variable]);
    }
}
