<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Serializers;

use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\JsonApiSerializer;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Core\Tests\Resources\CLoremIpsum;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CUserLog;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class JsonApiSerializerTest extends OxUnitTestCase
{
    public function testSerializeItemObject(): void
    {
        $resource = new Item(new CLoremIpsum(5, 'testlorem', 'foo_bar'), false);
        $resource->setType('loremIpsum');
        $resource->setSerializer(JsonApiSerializer::class);
        $serial = $resource->serialize();

        // Do not check details in meta because of timestamps
        $this->assertArrayHasKey('meta', $serial);
        // No links for object or array
        $this->assertArrayNotHasKey('links', $serial);
        // No relations for object or array
        $this->assertArrayNotHasKey('relations', $serial);

        $this->assertEquals(
            [
                'type'       => 'loremIpsum',
                'id'         => 5,
                'attributes' => [
                    'id'      => 5,
                    'type'    => 'testlorem',
                    'libelle' => 'foo_bar',
                ],
            ],
            $serial['data']
        );
    }

    public function testSerializeCollectionObject(): void
    {
        $resource = new Collection(
            [new CLoremIpsum(5, 'testlorem', 'foo_bar'), new CLoremIpsum(2, 'hey_there', 'bar_foo')]
        );
        $resource->setType('Ipsum');
        $resource->setSerializer(JsonApiSerializer::class);
        $serial = $resource->serialize();

        // Do not check details in meta because of timestamps
        $this->assertArrayHasKey('meta', $serial);
        // No links for object or array
        $this->assertArrayNotHasKey('links', $serial);
        // No relations for object or array
        $this->assertArrayNotHasKey('relations', $serial);

        $this->assertEquals(
            [
                [
                    'type'       => 'Ipsum',
                    'id'         => 5,
                    'attributes' => [
                        'id'      => 5,
                        'type'    => 'testlorem',
                        'libelle' => 'foo_bar',
                    ],
                ],
                [
                    'type'       => 'Ipsum',
                    'id'         => 2,
                    'attributes' => [
                        'id'      => 2,
                        'type'    => 'hey_there',
                        'libelle' => 'bar_foo',
                    ],
                ],
            ],
            $serial['data']
        );
    }

    public function testSerializeItemCMbObject(): void
    {
        $log      = $this->getUserLog();
        $resource = new Item($log);
        $resource->setModelRelations(RequestRelations::QUERY_KEYWORD_ALL);
        $resource->setRouter(RouterBridge::getInstance());
        $resource->setSerializer(JsonApiSerializer::class);

        $serial = $resource->serialize();

        $this->assertEquals(CUserLog::RESOURCE_TYPE, $serial['data']['type']);
        $this->assertArrayHasKey('attributes', $serial['data']);

        $this->assertArrayHasKey('relationships', $serial['data']);
        $user_id   = $serial['data']['relationships']['user']['data']['id'];
        $user_type = $serial['data']['relationships']['user']['data']['type'];

        $this->assertArrayHasKey('links', $serial['data']);
        $this->assertArrayHasKey('meta', $serial);

        $includes      = $serial['included'];
        $user_in_array = false;
        foreach ($includes as $_incl) {
            if ($_incl['id'] === $user_id && $_incl['type'] === $user_type) {
                $user_in_array = true;
                break;
            }
        }

        $this->assertTrue($user_in_array);
    }

    public function testSerializeCollectionCMbObject(): void
    {
        $log      = $this->getUserLog();
        $resource = new Collection([$log, $log]);

        $resource->setRouter(RouterBridge::getInstance());
        $resource->setModelRelations(RequestRelations::QUERY_KEYWORD_ALL);
        $resource->setSerializer(JsonApiSerializer::class);

        $serial = $resource->serialize();

        $users = [];
        foreach ($serial['data'] as $_data) {
            $this->assertEquals(CUserLog::RESOURCE_TYPE, $_data['type']);
            $this->assertArrayHasKey('attributes', $_data);
            $this->assertArrayHasKey('relationships', $_data);
            $this->assertArrayHasKey('links', $_data);

            $users[] = [
                'id'   => $_data['relationships']['user']['data']['id'],
                'type' => $_data['relationships']['user']['data']['type'],
            ];
        }

        $this->assertEquals(2, $serial['meta']['count']);
        $this->assertEquals($users[0], $users[1]);

        $this->assertCount(1, $serial['included']);
        $this->assertEquals($users[0]['id'], $serial['included'][0]['id']);
        $this->assertEquals($users[0]['type'], $serial['included'][0]['type']);
    }

    public function testSerializeItemFromArrayWithLinks(): void
    {
        $item = new Item(['_id' => 'test_id', '_type' => 'test_type', 'foo' => 'bar']);
        $item->addLinks(['lorem' => 'ipsum']);
        $serial = $item->serialize();

        $this->assertArrayNotHasKey('links', $serial);
        $this->assertArrayHasKey('links', $serial['data']);
    }

    public function testSerializeItemFromObjectWithLinks(): void
    {
        $obj       = new stdClass();
        $obj->id   = 'test_id';
        $obj->type = 'test_type';
        $obj->foo  = 'bar';

        $item = new Item($obj);
        $item->addLinks(['lorem' => 'ipsum']);
        $serial = $item->serialize();

        $this->assertEquals(['lorem' => 'ipsum'], $serial['data']['links']);
        $this->assertArrayNotHasKey('links', $serial);
    }

    public function testSerializeCollectionWithLinks(): void
    {
        $collection = new Collection(
            [
                ['_id' => 'test_id', '_type' => 'test_type', 'foo' => 'bar'],
                ['_id' => 'test_id2', '_type' => 'test_type', 'foo' => 'bar2'],
            ]
        );
        $collection->addLinks(['lorem' => 'ipsum']);
        $serial = $collection->serialize();

        $this->assertEquals(['lorem' => 'ipsum'], $serial['links']);
    }

    private function getUserLog(): CUserLog
    {
        $current_user_id = CUser::get()->_id;

        $log               = new CUserLog();
        $log->user_log_id  = 1;
        $log->_id          = 1;
        $log->type         = 'store';
        $log->user_id      = $current_user_id;
        $log->object_class = 'CUser';
        $log->object_id    = $current_user_id;

        return $log;
    }
}
