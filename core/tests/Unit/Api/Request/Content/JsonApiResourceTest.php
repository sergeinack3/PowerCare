<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\JsonApiResource;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Tests\OxUnitTestCase;

class JsonApiResourceTest extends OxUnitTestCase
{
    /** @var int */
    private const DATA_LIMIT = 10;

    public function testConstructFailed(): void
    {
        $this->expectException(RequestContentException::class);
        $this->getJsonApiWithLimit("toto");
    }

    public function testConstructFailedJsonMalFormatted(): void
    {
        $json_malformatted = [
            'data' => [
                [
                    'type'       => 'lorem',
                    'attributes' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ];
        $this->expectException(RequestContentException::class);
        $this->getJsonApiWithLimit(json_encode($json_malformatted));
    }

    /**
     * @throws RequestContentException
     */
    public function testConstructItem(): JsonApiResource
    {
        $json = $this->getJsonApi();
        $res  = $this->getJsonApiWithLimit($json);
        $this->assertCount(1, $res);
        $this->assertFalse($res->isCollection());

        return $res;
    }

    private function getJsonApi(int $count_item = 1): string
    {
        $data = [];
        for ($i = 1; $i <= $count_item; $i++) {
            $data['data'][] = [
                'type'       => 'lorem_' . $i,
                'id'         => $i,
                'attributes' => [
                    'foo'  => 'bar_' . $i,
                    'toto' => 'tata_' . $i,
                ],
                'meta'       => [
                    'token' => 007,
                ],
            ];
        }
        $data['data'] = $count_item === 1 ? reset($data['data']) : $data['data'];
        $data['meta'] = ['token' => 007];

        return json_encode($data);
    }

    /**
     * @throws RequestContentException
     */
    public function testConstructCollection(): void
    {
        $count_item = rand(2, 10);
        $json       = $this->getJsonApi($count_item);
        $res        = $this->getJsonApiWithLimit($json);
        $this->assertCount($count_item, $res);
        $this->assertTrue($res->isCollection());
    }

    /**
     * @throws RequestContentException
     */
    public function testConstructFailedCreateItemsBecauseOfLimit(): void
    {
        $json = $this->getJsonApi(self::DATA_LIMIT + 1);

        $this->expectExceptionObject(RequestContentException::tooManyObjects(self::DATA_LIMIT));
        $this->getJsonApiWithLimit($json);
    }

    /**
     * @throws RequestContentException
     */
    public function testConstructSuccessCreateItemsOnLimit(): void
    {
        $count_item = (self::DATA_LIMIT);
        $json       = $this->getJsonApi($count_item);

        $res = $this->getJsonApiWithLimit($json);
        $this->assertCount($count_item, $res);
        $this->assertTrue($res->isCollection());
    }

    /**
     * @param JsonApiResource $res
     *
     * @depends testConstructItem
     */
    public function testGetItem(JsonApiResource $res): void
    {
        $this->assertInstanceOf(JsonApiItem::class, $res->getItem());
    }

    /**
     * @param JsonApiResource $res
     *
     * @depends testConstructItem
     *
     */
    public function testGetMeta(JsonApiResource $res): void
    {
        $this->assertEquals($res->getMeta(), ['token' => 007]);
    }

    /**
     * @throws RequestContentException
     */
    private function getJsonApiWithLimit(string $json): JsonApiResource
    {
        $mock = $this->getMockBuilder(JsonApiResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLimit'])
            ->getMock();

        // We need to force getLimit to return a lower limit only for test cases
        $mock->expects($this->any())->method('getLimit')->willReturn(self::DATA_LIMIT);

        /**
         * We need to call the construct method after we set the return of getLimit method
         * because createItems is called in the construct and need the data limit
         */
        $mock->__construct($json);

        return $mock;
    }
}
