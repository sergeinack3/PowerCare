<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\Content\JsonApiResource;
use Ox\Core\Api\Request\Content\RequestContent;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestContentTest extends OxUnitTestCase
{
    private function makeRequest($content, $is_jsonapi = false)
    {
        $server = $is_jsonapi ? ['HTTP_Content-type' => RequestFormats::FORMAT_JSON_API] : [];

        return new Request([], [], [], [], [], $server, $content);
    }

    private function getJsonApi()
    {
        return json_encode([
                               'data' => [
                                   [
                                       'type'       => 'lorem',
                                       'id'         => 123,
                                       'attributes' => [
                                           'foo'  => 'bar',
                                           'toto' => 'tata',
                                       ],
                                       'meta'       => [
                                           'token' => 007,
                                       ],
                                   ],
                               ],
                           ]);
    }

    public function testGetRawContent()
    {
        $content = 'lorem-ipsum-dolor-set';

        $request_content = new RequestContent($this->makeRequest($content));


        $this->assertEquals($request_content->getRawContent(), $content);
    }

    public function testIsJsonContentFalse()
    {
        $content = 'lorem-ipsum-dolor-set';

        $request_content = new RequestContent($this->makeRequest($content));


        $this->assertFalse($this->invokePrivateMethod($request_content, 'isJsonContent'));
    }

    public function testIsJsonContent()
    {
        $content = json_encode(['lorem', 'ipsum', 'dolor', 'set']);

        $request_content = new RequestContent($this->makeRequest($content));

        $this->assertTrue($this->invokePrivateMethod($request_content, 'isJsonContent'));
    }

    public function testIsJsonApi()
    {
        $content = $this->getJsonApi();
        $req     = $this->makeRequest($content, true);

        $request_content = new RequestContent($req);

        $this->assertTrue($this->invokePrivateMethod($request_content, 'isJsonApi'));
    }

    public function testGetJsonApiResourceException()
    {
        $req             = $this->makeRequest('toto');
        $request_content = new RequestContent($req);
        $this->expectException(RequestContentException::class);
        $request_content->getJsonApiResource();
    }

    public function testGetJsonApiResource()
    {
        $req             = $this->makeRequest($this->getJsonApi(), true);
        $request_content = new RequestContent($req);
        $this->assertInstanceOf(JsonApiResource::class, $request_content->getJsonApiResource());
    }

    public function testGetContent()
    {
        $datas      = [
            'lorem' => 'ipsum',
            'foo'   => 'bar',
        ];
        $datas_json = json_encode($datas);

        $req             = $this->makeRequest($datas_json, false);
        $request_content = new RequestContent($req);
        $content         = $request_content->getContent(true);
        $this->assertEquals($datas, $content);
    }
}
