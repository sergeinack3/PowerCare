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

class CollectionTest extends OxUnitTestCase
{
    public function testConstructWithNoItemObjects(): void
    {
        $this->expectExceptionObject(new TestsException('Only Ox\\Tests\\JsonApi\\Item objects can be add to the collection'));
        new Collection(['test' => 'toto']);
    }

    public function testCreateFromArrayNoData(): void
    {
        $this->expectExceptionObject(new TestsException('Data must be the first key of the collection'));
        Collection::createFromArray([]);
    }

    public function testCreateFromArrayEmptyData(): void
    {
        $collection = Collection::createFromArray([Collection::DATA => []]);

        $this->assertEmpty($collection->getItems());
        $this->assertEmpty($collection->getLinks());
        $this->assertEmpty($collection->getMetas());
        $this->assertEmpty($collection->getIncluded());
    }

    public function testCreateFromArray(): Collection
    {
        $collection = Collection::createFromArray($this->getCollectionArray());

        $item1 = new Item('type1', 'id1');
        $item1->setInCollection(true);
        $item2 = (new Item('type2', 'id2'))->setAttributes(['foo' => 'bar']);
        $item2->setInCollection(true);

        $this->assertEquals([$item1, $item2], $collection->getItems());
        $this->assertEquals(
            ['link1' => 'link1_value', 'link2' => 'link2_value'],
            $collection->getLinks()
        );
        $this->assertEquals('link1_value', $collection->getLink('link1'));
        $this->assertEquals(['meta1' => 'meta1_value'], $collection->getMetas());
        $this->assertEquals('meta1_value', $collection->getMeta('meta1'));
        $this->assertEquals(
            [
                Item::createFromArray(['type' => 'included1_type', 'id' => 'included1_id'], true),
                Item::createFromArray(['type' => 'included2_type', 'id' => 'included2_id'], true),
            ],
            $collection->getIncluded()
        );


        return $collection;
    }

    /**
     * @depends testCreateFromArray
     */
    public function testJsonSerialize(Collection $collection): void
    {
        $array = $this->getCollectionArray();
        unset($array[Collection::INCLUDED]);

        $this->assertEquals(json_encode($array), json_encode($collection));
    }

    private function getCollectionArray(): array
    {
        return [
            Collection::DATA => [
                [
                    Item::TYPE => 'type1',
                    Item::ID => 'id1',
                ],
                [
                    Item::TYPE => 'type2',
                    Item::ID => 'id2',
                    Item::ATTRIBUTES => [
                        'foo' => 'bar',
                    ]
                ]
            ],
            Collection::LINKS => [
                'link1' => 'link1_value',
                'link2' => 'link2_value'
            ],
            Collection::META => [
                'meta1' => 'meta1_value',
            ],
            Collection::INCLUDED => [
                ['type' => 'included1_type', 'id' => 'included1_id'],
                ['type' => 'included2_type', 'id' => 'included2_id'],
            ],
        ];
    }
}
