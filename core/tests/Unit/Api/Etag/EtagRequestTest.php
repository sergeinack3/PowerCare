<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Etag;

use Ox\Core\Kernel\Event\EtagListener;
use Ox\Core\Tests\Unit\Api\UnitTestRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group schedules
 * @runTestsInSeparateProcesses
 */
class EtagRequestTest extends UnitTestRequest
{
    public function setUp(): void
    {
        $this->addListener(new EtagListener());
    }

    public function testRequestWithoutEtagResponseWithoutEtag()
    {
        $request = Request::create('/lorem/ispum');

        $request->attributes->add([
                                      '_route'      => 'lorem',
                                      '_controller' => EtagController::class . '::responseWithoutEtag',
                                  ]);

        $response = $this->handleRequest($request);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull($response->getContent());
        $this->assertNull($response->getEtag());
    }


    public function testRequestWithoutEtagResponseWithEtag()
    {
        $request = Request::create('/lorem/ispum');

        $request->attributes->add([
                                      '_route'      => 'lorem',
                                      '_controller' => EtagController::class . '::responseWithEtag',
                                  ]);

        $response      = $this->handleRequest($request);
        $response_etag = str_replace('"', '', $response->getEtag());

        $this->assertEquals($response_etag, EtagController::makeEtag());
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull($response->getContent());
    }

    public function testRequestWithEtagResponseWithInvalidEtag()
    {
        $request = Request::create('/lorem/ispum');
        $request->headers->set('if_none_match', '1234536789AZERTY');
        $request->attributes->add([
                                      '_route'      => 'lorem',
                                      '_controller' => EtagController::class . '::responseWithEtag',
                                  ]);

        $response = $this->handleRequest($request);

        $this->assertNotNull($response->getEtag());
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull($response->getContent());
    }

    public function testRequestWithEtagResponseWithEtag()
    {
        $request = Request::create('/api/lorem/ispum?filter=1');
        $request->attributes->add([
                                      '_route'      => 'lorem',
                                      '_controller' => EtagController::class . '::responseWithEtag',
                                  ]);

        // first request
        $response_1    = $this->handleRequest($request);
        $response_etag = str_replace('"', '', $response_1->getEtag());

        $this->assertNotNull($response_etag);
        $this->assertEquals($response_1->getStatusCode(), 200);
        $this->assertNotNull($response_1->getContent());

        $request->headers->set('if_none_match', $response_etag);
        $response_2 = $this->handleRequest($request);
        $this->assertEquals(304, $response_2->getStatusCode());
        $this->assertEquals($response_2->getContent(), '{}');
    }
}
