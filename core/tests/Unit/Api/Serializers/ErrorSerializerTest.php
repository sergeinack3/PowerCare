<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Serializers;

use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\ErrorSerializer;
use Ox\Tests\OxUnitTestCase;

class ErrorSerializerTest extends OxUnitTestCase
{
    public function testSerializeItem()
    {
        $resource = new Item(
            [
                '_type' => 'type_test',
                '_id'   => 'id_test',
                'foo'   => 'bar',
            ]
        );
        $resource->setSerializer(ErrorSerializer::class);
        $serial = $resource->serialize();

        $this->assertEquals(
            [
                'errors' => [
                    'foo' => 'bar',
                ],
            ],
            $serial
        );
    }

    public function testSerializeCollection()
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

        $resource->setSerializer(ErrorSerializer::class);
        $serial = $resource->serialize();

        $this->assertEquals(
            [
                [
                    'errors' => [
                        'foo' => 'bar',
                    ],
                ],
                [
                    'errors' => [
                        'foo' => 'bar1',
                    ],
                ],
            ],
            $serial
        );
    }
}
