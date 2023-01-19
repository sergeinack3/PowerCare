<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit;

use Ox\Mediboard\Jfse\Tests\Unit\Controllers\TestJfseAbstractController;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the methods of the AbstractController class
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit
 */
class JfseControllerTest extends OxUnitTestCase
{
    /**
     * Checks that when an instance of a controller with an unknon route is created, a RouteNotFoundException is thrown
     */
    public function testRouteNotFound(): void
    {
        $this->expectExceptionMessage('RouteNotFound');
        new TestJfseAbstractController('testRouteNotFound');
    }

    /**
     * Checks that the name of the method is returned by the method AbstractController::getMethod
     */
    public function testMethodFound(): void
    {
        $controller = new TestJfseAbstractController('testMethodFound');
        $this->assertEquals('testMethodFound', $controller->getMethod());
    }

    /**
     * Checks that an MethodNotFoundException is thrown when no method is set in the routes array
     */
    public function testMethodNotFound(): void
    {
        $this->expectExceptionMessage('MethodNotFound');
        $controller = new TestJfseAbstractController('testMethodNotFound');
        $controller->getMethod();
    }

    /**
     * Checks that an MethodNotFoundException is thrown when the method in the routes does not exist
     */
    public function testMethodNotExists(): void
    {
        $this->expectExceptionMessage('MethodNotFound');
        $controller = new TestJfseAbstractController('testMethodNotExists');
        $controller->getMethod();
    }

    /**
     * Checks that a Request object is returned by the method AbstractController::getRequest
     */
    public function testRequestMethodFound(): void
    {
        $controller = new TestJfseAbstractController('testMethodFound');
        $this->assertInstanceOf(Request::class, $controller->getRequest());
    }

    /**
     * Checks that an MethodNotFoundException is thrown when no request method is set in the routes array
     */
    public function testRequestMethodNotFound(): void
    {
        $this->expectExceptionMessage('MethodNotFound');
        $controller = new TestJfseAbstractController('testMethodNotFound');
        $controller->getRequest();
    }

    /**
     * Checks that an MethodNotFoundException is thrown when the request method does not exist
     */
    public function testRequestMethodNotExists(): void
    {
        $this->expectExceptionMessage('MethodNotFound');
        $controller = new TestJfseAbstractController('testMethodNotExists');
        $controller->getRequest();
    }

    /**
     * Checks that an  is thrown when the request method does not return a Request object
     */
    public function testInvalidRequest(): void
    {
        $this->expectExceptionMessage('InvalidRequest');
        $controller = new TestJfseAbstractController('testInvalidRequest');
        $controller->getRequest();
    }

    /**
     * Test if the prefix returns a string
     */
    public function testRoutePrefixReturnsAString(): void
    {
        $this->assertIsString(TestJfseAbstractController::getRoutePrefix());
    }

    /**
     * Test if the prefix returns a non empty string
     */
    public function testRoutePrefixReturnsANonEmptyString(): void
    {
        $this->assertNotEmpty(TestJfseAbstractController::getRoutePrefix());
    }
}
