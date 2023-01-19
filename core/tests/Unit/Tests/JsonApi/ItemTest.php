<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Tests\JsonApi;

use Ox\Tests\JsonApi\Collection;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class ItemTest extends OxUnitTestCase
{
    public function testCreateFromArrayNoData(): void
    {
        $this->expectExceptionObject(new TestsException('Data must be the first key of the Item'));
        Item::createFromArray([]);
    }

    public function testCreateFromArrayNoType(): void
    {
        $this->expectExceptionObject(new TestsException('Type is mandatory to create an Item'));
        Item::createFromArray([Item::DATA => [Item::ID => 10]]);
    }

    public function testCreateFromArrayNoId(): void
    {
        $this->expectExceptionObject(new TestsException('Id is mandatory to create an Item'));
        Item::createFromArray([Item::DATA => [Item::TYPE => 'foo']]);
    }

    public function testCreateMinimalItem(): void
    {
        $item = Item::createFromArray([Item::DATA => [Item::ID => 'id', Item::TYPE => 'type']]);

        $this->assertEquals('id', $item->getId());
        $this->assertEquals('type', $item->getType());
        $this->assertEmpty($item->getAttributes());
        $this->assertEmpty($item->getRelationships());
        $this->assertEmpty($item->getLinks());
        $this->assertEmpty($item->getMetas());
        $this->assertEmpty($item->getIncluded());
    }

    public function testCreateFromArray(): Item
    {
        $relation1 = Collection::createFromArray(
            [
                'data' => [
                    ['type' => 'relation1_type', 'id' => 'relation1_id'],
                    ['type' => 'relation1_type_bis', 'id' => 'relation1_id_bis'],
                ],
            ]
        );

        $item = Item::createFromArray($this->getArrayItem());
        $this->assertEquals('foo', $item->getType());
        $this->assertEquals('bar', $item->getId());
        $this->assertEquals(
            ['attr1' => 'attr1_value', 'attr2' => 'attr2_value'],
            $item->getAttributes()
        );
        $this->assertEquals('attr2_value', $item->getAttribute('attr2'));
        $this->assertEquals(
            [
                'relation1' => $relation1,
                'relation2' => Item::createFromArray(['data' => ['type' => 'relation2_type', 'id' => 'relation2_id']]),
            ],
            $item->getRelationships()
        );
        $this->assertEquals($relation1, $item->getRelationship('relation1'));
        $this->assertEquals(
            ['link1' => 'link1_value', 'link2' => 'link2_value'],
            $item->getLinks()
        );
        $this->assertEquals('link2_value', $item->getLink('link2'));
        $this->assertEquals(
            ['meta1' => 'meta1_value', 'meta2' => 'meta2_value'],
            $item->getMetas()
        );
        $this->assertEquals('meta1_value', $item->getMeta('meta1'));
        $this->assertEquals(
            [Item::createFromArray(['type' => 'included1_type', 'id' => 'included1_id'], true)],
            $item->getIncluded()
        );

        return $item;
    }

    /**
     * @depends testCreateFromArray
     */
    public function testJsonSerialize(Item $item): void
    {
        $array = $this->getArrayItem();
        unset($array['included']);

        $this->assertEquals(json_encode($array), json_encode($item->jsonSerialize()));
    }

    private function getArrayItem(): array
    {
        return [
            Item::DATA     => [
                Item::TYPE          => 'foo',
                Item::ID            => 'bar',
                Item::ATTRIBUTES    => [
                    'attr1' => 'attr1_value',
                    'attr2' => 'attr2_value',
                ],
                Item::RELATIONSHIPS => [
                    'relation1' => [
                        Item::DATA => [
                            [
                                Item::TYPE => 'relation1_type',
                                Item::ID   => 'relation1_id',
                            ],
                            [
                                Item::TYPE => 'relation1_type_bis',
                                Item::ID   => 'relation1_id_bis',
                            ],
                        ],
                    ],
                    'relation2' => [
                        Item::DATA => [
                            Item::TYPE => 'relation2_type',
                            Item::ID   => 'relation2_id',
                        ],
                    ],
                ],
                Item::LINKS         => [
                    'link1' => 'link1_value',
                    'link2' => 'link2_value',
                ],
            ],
            Item::META     => [
                'meta1' => 'meta1_value',
                'meta2' => 'meta2_value',
            ],
            Item::INCLUDED => [
                [
                    Item::TYPE => 'included1_type',
                    Item::ID   => 'included1_id',
                ],
            ],
        ];
    }
}
