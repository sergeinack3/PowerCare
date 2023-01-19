<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional\Event;

use Ox\Core\Auth\Authenticators\ApiTokenAuthenticator;
use Ox\Core\Kernel\Event\CorsListener;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests for the CorsListener :
 * - onRequest : For OPTIONS request change response to 204 and stop the propagation
 * - onResponse : Set access control headers
 */
class CorsListenerTest extends OxWebTestCase
{
    public function testOnRequestSetResponse(): Request
    {
        $kernel = static::createKernel();

        $request = $this->createRequestApi();
        $request->server->set('REQUEST_METHOD', Request::METHOD_OPTIONS);

        $listener = new CorsListener();
        $this->assertFalse($listener->isRequestOption());

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $this->assertNull($event->getResponse());

        $listener->onRequest($event);

        $this->assertTrue($listener->isRequestOption());

        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        return $request;
    }

    /**
     * @depends testOnRequestSetResponse
     */
    public function testOnResponseSetHeaders(Request $request): void
    {
        $listener = new CorsListener();
        $kernel = static::createKernel();
        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );
        $listener->onResponse($event);

        $response = $event->getResponse();

        $this->assertEquals('GET', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals(
            'Accept, Content-Type, Authorization, ' . ApiTokenAuthenticator::TOKEN_HEADER_KEY,
            $response->headers->get('Access-Control-Allow-Headers')
        );
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
    }
}
