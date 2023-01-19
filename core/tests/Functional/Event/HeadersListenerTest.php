<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional\Event;

use Ox\Core\CApp;
use Ox\Core\Kernel\Event\HeadersListener;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Test the adding of custom headers to the response
 */
class HeadersListenerTest extends OxWebTestCase
{
    public function testOnResponseAddHeaders(): void
    {
        $listener = new HeadersListener();
        $kernel = static::createKernel();

        $response = new Response(null, 200, ['lorem' => 'ipsum', 'Pragma' => 'test']);
        $event    = new ResponseEvent(
            $kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener->onResponse($event);

        $response = $event->getResponse();

        $this->assertEquals('ipsum', $response->headers->get('lorem'));
        $this->assertEquals(HeadersListener::EXPIRATION_DATETIME, $response->headers->get('Expires'));

        foreach (explode(', ', HeadersListener::CACHE_CONTROL) as $cache_instruction) {
            $this->assertStringContainsString($cache_instruction, $response->headers->get('Cache-Control'));
        }

        $this->assertEquals(HeadersListener::PRAGMA, $response->headers->get('Pragma'));
        $this->assertEquals(HeadersListener::COMPATIBILITY, $response->headers->get('X-UA-Compatible'));
        $this->assertEquals(CApp::getRequestUID(), $response->headers->get('X-Request-ID'));

        // Because of the timestamp created we cannot assert a specific value
        $this->assertNotNull($response->headers->get('Last-Modified'));
    }
}
