<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Resources;

use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Transformers\AbstractTransformer;
use Ox\Core\Api\Transformers\ArrayTransformer;
use Ox\Core\Api\Transformers\ModelObjectTransformer;
use Ox\Core\Api\Transformers\ObjectTransformer;
use Ox\Core\Tests\Resources\CLoremIpsum;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;

class TransformersTest extends OxUnitTestCase
{
    /**
     * @dataProvider provideResources
     */
    public function testConstructTransformers(AbstractResource $resource, $transformer_class)
    {
        $transformer = $this->invokePrivateMethod($resource, 'createTransformer');
        $this->assertInstanceOf($transformer_class, $transformer);
    }


    /**
     * @dataProvider provideResources
     */
    public function testCreateDatas(AbstractResource $resource, $transformer_class)
    {
        /** @var AbstractTransformer $transformer */
        $transformer = $this->invokePrivateMethod($resource, 'createTransformer');
        $datas       = $transformer->createDatas();
        $this->assertIsArray($datas);
        $this->assertArrayHasKey('datas', $datas);
        $this->assertArrayHasKey('_type', $datas['datas']);
        $this->assertArrayHasKey('_id', $datas['datas']);
    }

    public function provideResources()
    {
        return [
            'resource_array'        => [
                new Item(['foo' => 'bar']),
                ArrayTransformer::class,
            ],
            'resource_object'       => [
                new Item(new CLoremIpsum(123, 'toto', 'tata')),
                ObjectTransformer::class,
            ],
            'resource_model_object' => [
                new Item(new CUser()),
                ModelObjectTransformer::class,
            ],
        ];
    }

    public function testCreateId()
    {
        $item       = new Item(['foo' => 'bar']);
        $transfomer = $this->invokePrivateMethod($item, 'createTransformer');
        $id         = $this->invokePrivateMethod($transfomer, 'createIdFromData', 'loremipsum');
        $this->assertEquals($id, 'a1a9b039cffc4137f69c065b8978765b');
    }
}
