<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional\Event;

use Ox\Core\Api\Request\Etags;
use Ox\Core\Kernel\Event\EtagListener;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * ETags listener :
 * - onRequest : Get request ETags
 * - onResponse : If response's ETag is in the request ETags, return notModifiedResponse
 */
class EtagListenerTest extends OxWebTestCase
{
    private const ETAG = 'loremipsum';

    public function testOnRequestGetEtags(): array
    {
        $listener = new EtagListener();
        $kernel = static::createKernel();

        $request = $this->createRequestApi();
        $request->headers->set('If-None-Match', self::ETAG);

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $this->assertNull($listener->getRequestEtags());

        $listener->onRequest($event);

        $this->assertEquals(new Etags([self::ETAG]), $listener->getRequestEtags());

        return [$listener, $request, $kernel];
    }

    /**
     * @depends testOnRequestGetEtags
     */
    public function testOnResponseReturnNotModifiedResponse(array $args): void
    {
        [$listener, $request, $kernel] = $args;

        $response = new Response(null, 200, ['ETag' => self::ETAG]);
        $event    = new ResponseEvent(
            $kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response
        );

        $listener->onResponse($event);

        $event_response = $event->getResponse();
        $this->assertNotEquals($response, $event_response);
        $this->assertEquals(Response::HTTP_NOT_MODIFIED, $event_response->getStatusCode());
        $this->assertEquals('"' . self::ETAG . '"', $event_response->getEtag());
    }
}
