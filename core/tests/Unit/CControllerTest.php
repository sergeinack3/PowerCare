<?php

namespace Ox\Core\Tests\Unit;

use Exception;
use InvalidArgumentException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CModelObjectCollection;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Mediboard\Admin\Controllers\PermissionController;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Mediboard\System\Tests\Classes\SourceTestTrait;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CControllerTest extends OxUnitTestCase
{
    use SourceTestTrait;

    private const CONTROLLER      = CFHIRController::class;
    private const FUNCTION        = 'initBlink1';
    private const FUNCTION_FAILED = 'failedTestFunction';
    private const MODULE          = 'fhir';
    private const ROUTE           = 'fhir_metadata';
    /**
     * RenderResponse
     */
    public function testRenderResponse(): void
    {
        $controller = $this->getController();
        $content    = 'Lorem ipsum';
        $response   = $controller->renderResponse($content);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getContent(), $content);
    }

    /**
     * GetModule
     *
     * @throws Exception
     */
    public function testGetModule(): void
    {
        $controller = $this->getController();
        $module     = $controller->getModuleName();
        $this->assertEquals($module, self::MODULE);
    }

    /**
     * GetControllerFromRequest
     *
     * @throws ControllerException
     */
    public function testGetControllerFromRequest(): void
    {
        $route = new Route('/lorem/ipsum');
        $route->setDefault('_controller', self::CONTROLLER);
        $collection = new RouteCollection();
        $collection->add('a', $route);

        $request    = Request::create('/lorem/ipsum');
        $matcher    = new UrlMatcher($collection, new RequestContext());
        $parameters = $matcher->matchRequest($request);
        $request->attributes->add($parameters);

        $controller = CController::getControllerFromRequest($request);
        $this->assertEquals($controller, $this->getController());
    }

    /**
     *
     */
    public function testGetReflectionMethod(): void
    {
        $controller = self::CONTROLLER;
        /** @var CController $controller */
        $controller = new $controller();
        $this->assertInstanceOf(ReflectionMethod::class, $controller->getReflectionMethod(self::FUNCTION));
    }

    /**
     *
     */
    public function testGetReflectionClass(): void
    {
        $controller = self::CONTROLLER;
        /** @var CController $controller */
        $controller = new $controller();
        $this->assertInstanceOf(ReflectionClass::class, $controller->getReflectionClass());
    }

    /**
     * RenderJsonRespons
     */
    public function testRenderJsonResponse(): void
    {
        $controller = $this->getController();
        $content    = ['foo' => 'bar'];
        $response   = $controller->renderJsonResponse($content, 200, [], false);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($response->getContent(), json_encode($content));
    }

    /**
     * GetControllerFromRouteOk
     */
    public function testGetControllerFromRouteOk(): void
    {
        $this->assertInstanceOf(CController::class, CController::getControllerFromRoute($this->getRoute()));
    }

    /**
     * GetControllerFromRouteKo
     */
    public function testGetControllerFromRouteKo(): void
    {
        $this->expectException(ControllerException::class);
        CController::getControllerFromRoute(new Route('api', []));
    }

    /**
     * GetMethodFromRouteOk
     */
    public function testGetMethodFromRouteOk(): void
    {
        $this->assertEquals(self::FUNCTION, CController::getMethodFromRoute($this->getRoute()));
    }

    /**
     * GetMethodFromRouteKo
     */
    public function testGetMethodFromRouteKo(): void
    {
        $this->expectException(ControllerException::class);
        CController::getMethodFromRoute($this->getRouteFailed());
    }

    /**
     * @return CController
     */
    private function getController(): CController
    {
        $controller = self::CONTROLLER;

        return new $controller();
    }

    /**
     * @return Route
     */
    private function getRoute(): Route
    {
        return new Route('http::localhost', ['_controller' => self::CONTROLLER . '::' . self::FUNCTION]);
    }

    /**
     * @return Route
     */
    private function getRouteFailed(): Route
    {
        return new Route('http::localhost', ['_controller' => self::CONTROLLER . '::' . self::FUNCTION_FAILED]);
    }


    public function testRedirect(): void
    {
        $controller = new PermissionController();
        $target_url = 'http://www.loremi-ipsum.fr';
        /** @var RedirectResponse $response */
        $response = $this->invokePrivateMethod($controller, 'redirect', $target_url);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($target_url, $response->getTargetUrl());
    }

    public function testRedirectToRoute(): void
    {
        $controller = new PermissionController();
        /** @var RedirectResponse $response */
        $response = $this->invokePrivateMethod($controller, 'redirectToRoute', 'admin_identicate', [], 302);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($controller->generateUrl('admin_identicate'), $response->getTargetUrl());
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRedirectToRouteFailed(): void
    {
        $controller = new PermissionController();
        $this->expectException(InvalidArgumentException::class);
        $this->invokePrivateMethod($controller, 'redirectToRoute', 'system_about', [], 404);
    }

    public function testStore(): void
    {
        $controller = new SystemController();
        /** @var CUser $model */
        $model                = $this->getObjectFromFixturesReference(
            CUser::class,
            UsersFixtures::REF_USER_LOREM_IPSUM
        );
        $address1             = uniqid('address');
        $model->user_address1 = $address1;

        $collection = new CModelObjectCollection();
        $collection->add($model);

        /** @var Item $item */
        $item = $this->invokePrivateMethod($controller, 'storeCollection', $collection);
        $this->assertEquals($item->getDatas()[0]->user_address1, $address1);
    }

    public function testSelfRequestSourceDisabled(): void
    {
        $source         = new CSourceHTTP();
        $source->active = 0;
        $controller     = new SystemController();

        $this->expectExceptionObject(
            new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, CAppUI::tr('access-forbidden'))
        );
        $this->invokePrivateMethod($controller, 'selfRequest', $source);
    }

    public function testSelfRequestUnautorized(): void
    {
        $source = $this->getMockBuilder(CSourceHTTP::class)
            ->disableOriginalConstructor()
            ->getMock();
        $source->host = '127.0.0.1';
        $source->active = 1;

        $response = new \Nyholm\Psr7\Response(Response::HTTP_UNAUTHORIZED, [], 'loremp ipsum');
        $this->mockSourceForResponse($response, $source);

        $controller = new SystemController();
        $this->expectExceptionObject(
            new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, CAppUI::tr('access-forbidden'))
        );

        $this->invokePrivateMethod($controller, 'selfRequest', $source);
    }

    public function testPrepareHeaderFromResponse(): void
    {
        $response_headers = [
            'Server'            => "test",
            'X-Powered-By'      => "test",
            'X-Mb-RequestInfo'  => "test",
            'Set-Cookie'        => "test",
            'Transfer-Encoding' => "test",
        ];

        $this->assertEmpty(
            $this->invokePrivateMethod($this->getController(), 'prepareHeaderFromResponse', $response_headers)
        );
    }


    public function testRenderXmlResponse(): void
    {
        $controller = $this->getController();
        $content    = $controller->renderXmlResponse(['lorem' => "ipsum"])->getContent();

        $this->assertStringContainsString('<lorem>ipsum</lorem>', $content);
    }
}
