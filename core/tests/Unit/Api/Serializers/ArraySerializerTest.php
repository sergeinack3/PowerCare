<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Serializers;

use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\ArraySerializer;
use Ox\Tests\OxUnitTestCase;

class ArraySerializerTest extends OxUnitTestCase
{
    public function testSerializeItem(): void
    {
        $resource = new Item(
            [
                '_type' => 'type_test',
                '_id'   => 'id_test',
                'foo'   => 'bar',
            ]
        );
        $resource->setSerializer(ArraySerializer::class);
        $serial = $resource->serialize();

        // Do not check details in meta because of timestamps
        $this->assertArrayHasKey('metas', $serial);
        // No links for array
        $this->assertTrue(empty($serial['links']));

        $this->assertEquals(
            [
                '_type' => 'type_test',
                '_id'   => 'id_test',
                'foo'   => 'bar',
            ],
            $serial['datas']
        );

        $this->assertArrayNotHasKey('relationships', $serial);
    }

    public function testSerializeCollection(): void
    {
        $resource = new Collection(
            [
                [
                    '_type' => 'type_test',
                    '_id'   => 'id_test',
                    'foo'   => 'bar',
                ],
                [
                    '_type' => 'type_test2',
                    '_id'   => 'test_bar',
                    'foo'   => 'bar1',
                ],
            ]
        );

        $resource->setSerializer(ArraySerializer::class);
        $serial = $resource->serialize();

        // Do not check details in meta because of timestamps
        $this->assertArrayHasKey('metas', $serial);
        // No links for array
        $this->assertTrue(empty($serial['links']));

        $this->assertEquals(
            [
                [
                    'datas' => [
                        '_type' => 'type_test',
                        '_id'   => 'id_test',
                        'foo'   => 'bar',
                    ],
                ],
                [
                    'datas' => [
                        '_type' => 'type_test2',
                        '_id'   => 'test_bar',
                        'foo'   => 'bar1',
                    ],
                ],
            ],
            $serial['datas']
        );
    }

    public function testSerializeWithRelationships(): void
    {
        $resource = new Item(
            [
                '_type' => 'type_test',
                '_id'   => 'id_test',
                'foo'   => 'bar',
            ]
        );

        $resource->setSerializer(ArraySerializer::class);

        $relation_content = [
            '_type' => 'type_relation',
            '_id'   => 'id_relation',
            'lorem' => 'ipsum',
        ];

        $resource->addAdditionalRelation(new Item($relation_content, false), 'relation_test');

        $serial = $resource->serialize();

        $this->assertEquals(
            [
                'relation_test' => [
                    [
                        'datas' => $relation_content,
                    ],
                ],
            ],
            $serial['relationships']
        );
    }
}
