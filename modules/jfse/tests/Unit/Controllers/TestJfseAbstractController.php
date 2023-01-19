<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Controllers;

use Ox\Mediboard\Jfse\Controllers\AbstractController;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestController
 */
class TestJfseAbstractController extends AbstractController
{
    /** @var array */
    protected static $routes = [
        'testMethodFound'     => [
            'method'  => 'testMethodFound',
            'request' => 'testRequestMethodFound',
        ],
        'testMethodNotFound'  => [],
        'testMethodNotExists' => [
            'method'  => 'methodNotExists',
            'request' => 'requestMethodNotExists',
        ],
        'testInvalidRequest'  => [
            'method'  => 'testMethodFound',
            'request' => 'requestMethodInvalidReturn',
        ],
    ];

    /**
     * Used for testing the method is set in the routes and exists
     *
     * @param Request $request
     *
     * @return Response
     */
    public function testMethodFound(Request $request): Response
    {
        return new SmartyResponse('');
    }

    /**
     * Used for testing the request method is set in the routes and exists
     *
     * @param array $data
     *
     * @return Request
     */
    public function testRequestMethodFound(array $data = []): Request
    {
        return new Request([], $data);
    }

    /**
     * Used for thesting that an exception is thrown when a requestMethod does not return a Request object
     *
     * @return string
     */
    public function requestMethodInvalidReturn(): string
    {
        return 'Not a Request object';
    }

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'testJfseController';
    }
}
