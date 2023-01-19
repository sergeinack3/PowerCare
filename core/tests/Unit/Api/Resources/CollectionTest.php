<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Resources;

use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Tests\Resources\CLoremIpsum;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Tests\OxUnitTestCase;
use TypeError;

class CollectionTest extends OxUnitTestCase
{
    public function testFailedConstructNotArray(): void
    {
        $this->expectException(TypeError::class);
        $collection = new Collection('lorem ipsum');
    }

    public function testConstructFromArray(): Collection
    {
        $datas      = [
            ['lorem' => 'ipsum'],
            ['foo' => 'bar'],
        ];
        $collection = new Collection($datas);
        $this->assertEquals($collection->getDatas(), $datas);
        $items = [];
        foreach ($datas as $data) {
            $items[] = new Item($data, false);
        }
        $this->assertEquals($collection->getItems(), $items);

        return $collection;
    }


    public function testConstructFromObject(): void
    {
        $datas      = [
            new CLoremIpsum(123, 'foo', 'testConstructFromObject'),
            new CLoremIpsum(123, 'foo', 'testConstructFromObject'),
        ];
        $collection = new Collection($datas);
        $this->assertEquals($collection->getDatas(), $datas);
    }

    public function testConstructFromCModelObject(): Collection
    {
        $users      = [
            CUser::getSampleObject(),
            CUser::getSampleObject(),
        ];
        $collection = new Collection($users);
        $this->assertEquals($collection->getDatas(), $users);

        return $collection;
    }


    private function createCModelObjectCollection(): Collection
    {
        $users = [
            CUser::getSampleObject(),
            CUser::getSampleObject(),
            CUser::getSampleObject(),
            CUser::getSampleObject(),
            CUser::getSampleObject(),
        ];

        return new Collection($users);
    }


    public function testCreateLinksPagination(): void
    {
        $collection = $this->createCModelObjectCollection();
        $links      = [
            "self"  => "?offset=10&limit=100",
            "next"  => "?limit=100&offset=110",
            "first" => "?limit=100&offset=0",
            "last"  => "?limit=100&offset=900",
        ];
        $collection->createLinksPagination(10, 100, 1000);
        $this->assertEquals($links, $collection->getLinks());

        $meta = $collection->getMetas();
        $this->assertEquals(1000, $meta[Collection::META_TOTAL]);
    }

    public function testCreateLinksPaginationWithoutTotalZero(): void
    {
        $collection = $this->createCModelObjectCollection();
        $links      = [
            "self"  => "?offset=0&limit=100",
            "first" => "?limit=100&offset=0",
            "last"  => "?limit=100&offset=0",
        ];
        $collection->createLinksPagination(0, 100, 0);
        $this->assertEquals($links, $collection->getLinks());

        $meta = $collection->getMetas();
        $this->assertEquals(0, $meta[Collection::META_TOTAL]);
    }

    public function testCreateLinksPaginationWithoutTotal(): void
    {
        $collection = $this->createCModelObjectCollection();
        $links      = [
            "self"  => "?offset=0&limit=100",
            "first" => "?limit=100&offset=0",
            "next"  => "?limit=100&offset=100",
        ];
        $collection->createLinksPagination(0, 100);
        $this->assertEquals($links, $collection->getLinks());

        $meta = $collection->getMetas();
        $this->assertArrayNotHasKey(Collection::META_TOTAL, $meta);
    }

    public function testPropageSettings(): void
    {
        $collection = $this->createCModelObjectCollection();
        $collection->setType('lorem');
        $collection->setModelFieldsets('none');
        $collection->setModelRelations('none');

        foreach ($collection as $item) {
            $this->invokePrivateMethod($collection, 'propageSettings', $item);
            $this->assertEquals($item->getType(), 'lorem');
            $this->assertEmpty($item->getModelFieldsets());
            $this->assertEmpty($item->getModelRelations());
        }
    }

    public function testPropageSettingsFieldsetsInDeep()
    {
        $collection = $this->createCModelObjectCollection();
        $collection->setType('lorem');
        $collection->setModelFieldsets(
            [
                CUser::FIELDSET_DEFAULT,
                CUser::FIELDSET_EXTRA,
                CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_DEFAULT,
                CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_EXTRA,
            ]
        );
        $collection->setModelRelations('none');

        foreach ($collection as $item) {
            $this->invokePrivateMethod($collection, 'propageSettings', $item);
            $this->assertEquals($item->getType(), 'lorem');
            $this->assertEquals($collection->getModelFieldsets(), $item->getModelFieldsets());
            $this->assertEmpty($item->getModelRelations());
        }
    }

    public function testIsIterable()
    {
        $collection = $this->createCModelObjectCollection();
        $items      = $collection->getItems();
        $this->assertIterableCount($collection, $items, count($items));
    }

    public function testIsCountable()
    {
        $collection = $this->createCModelObjectCollection();
        $this->assertCountableCount($collection, count($collection->getItems()));
    }

    public function testTransform()
    {
        $collection        = $this->createCModelObjectCollection();
        $datas_transformed = $collection->transform();

        $this->assertIsArray($datas_transformed);
        $this->assertCount(5, $datas_transformed);
        foreach ($datas_transformed as $datas) {
            $this->assertArrayHasKey('_type', $datas['datas']);
            $this->assertArrayHasKey('_id', $datas['datas']);
        }
    }

    public function testMetas()
    {
        $collection = $this->createCModelObjectCollection();
        $this->invokePrivateMethod($collection, 'setDefaultMetas');
        $metas = $collection->getMetas();
        $this->assertArrayHasKey('count', $metas);
        $this->assertEquals($metas['count'], 5);
    }
}
