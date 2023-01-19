<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use stdClass;

/**
 * Class CMbArrayTest
 */
class CMbArrayTest extends OxUnitTestCase
{
    /** @var CMbArray $stub */
    protected $stub;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stub = $this->getMockForAbstractClass(CMbArray::class);
    }

    public function testRemoveValue(): void
    {
        $a = $b = [1, '2', 2];

        $null = null;

        $count_non_strict = CMbArray::removeValue(2, $a);
        $count_strict     = CMbArray::removeValue(2, $b, true);
        $count_with_null  = CMbArray::removeValue(123, $null);

        $this->assertEquals([1], $a);
        $this->assertEquals([1, '2'], $b);
        $this->assertNull($null);

        $this->assertEquals(2, $count_non_strict);
        $this->assertEquals(1, $count_strict);
        $this->assertEquals(0, $count_with_null);
    }

    /**
     * Test CMbArray::compareKeys
     */
    public function testCompareKeysHasRightReturn(): void
    {
        $array1 = ["key1" => "val1", "key2" => "val2"];
        $array2 = ["key1" => "val1", "key2" => "val2"];
        $this->assertEmpty($this->stub->compareKeys($array1, $array2));

        $array1 = ["key1" => "val", "key2" => "val2"];
        $this->assertContains("different_values", $this->stub->compareKeys($array1, $array2));

        $array1 = ["key2" => "val2"];
        $this->assertContains("absent_from_array1", $this->stub->compareKeys($array1, $array2));

        $array1 = ["key1" => "val1", "key2" => "val2"];
        $array2 = ["key1" => "val1"];
        $this->assertContains("absent_from_array2", $this->stub->compareKeys($array1, $array2));
    }

    /**
     * Test CMbArray::diffRecursive
     */
    public function testDiffRecursiveHasRightReturn(): void
    {
        $this->assertFalse($this->stub->diffRecursive([], []));
        $this->assertFalse($this->stub->diffRecursive([1], [1]));
        $this->assertFalse($this->stub->diffRecursive([[], [2]], [[], [2]]));

        $array1 = [
            "key1" => "val1",
            "key2" => "val2",
            [
                "key3" => "val3",
                "key4" => "val4",
                [
                    "key5" => "valDiff",
                ],
            ],
            [
                "keyDiff" => "val6",
            ],
        ];

        $array2 = [
            "key1" => "val1",
            "key2" => "val2",
            [
                "key3" => "val3",
                "key4" => "val4",
                [
                    "key5" => "val5",
                ],
            ],
            [
                "key6" => "val6",
            ],
        ];

        $resArray = [
            [
                [
                    "key5" => "valDiff",
                ],
            ],
            [
                "keyDiff" => "val6",
            ],
        ];
        $this->assertEquals($resArray, $this->stub->diffRecursive($array1, $array2));

        $array1 = ["key" => "value", ["key" => "value"]];
        $this->assertEquals($array1, $this->stub->diffRecursive($array1, null));
        $this->assertEquals($array1, $this->stub->diffRecursive($array1, ["key"]));
        $this->assertEquals([2 => null], $this->stub->diffRecursive([1 => 1, 2 => null], [1 => 1]));
    }

    /**
     * Test CMbArray::removeValue
     */
    public function testRemoveValueHasRightReturn(): void
    {
        $array = [];
        $this->assertEquals(0, $this->stub->removeValue(0, $array));
        $array = [
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
        ];
        $this->assertEquals(1, $this->stub->removeValue("value2", $array));
        $array = [
            "key1" => "value1",
            "key2" => "value1",
            "key3" => "value3",
        ];
        $this->assertEquals(2, $this->stub->removeValue("value1", $array));
    }

    /**
     * Test CMbArray::getPrevNextKey
     */
    public function testGetPrevNextKeysHasRightReturn(): void
    {
        $resArray = [
            "prev" => null,
            "next" => "key2",
        ];
        $this->assertEquals($resArray, $this->stub->getPrevNextKeys(["key1" => "val1", "key2" => "val2"], "key1"));

        $resArray = [
            "prev" => "key1",
            "next" => "key3",
        ];
        $this->assertEquals(
            $resArray,
            $this->stub->getPrevNextKeys(
                ["key1" => "val1", "key2" => "val2", "key3" => "val3"],
                "key2"
            )
        );

        $resArray = [
            "prev" => "key1",
            "next" => null,
        ];
        $this->assertEquals(
            $resArray,
            $this->stub->getPrevNextKeys(["key1" => "val1", "key2" => "val2"], "key2")
        );
    }

    /**
     * Test CMbArray::mergeRecursive
     */
    public function testMergeRecursive(): void
    {
        $this->assertNull($this->stub->mergeRecursive(null, null));
        $this->assertNull($this->stub->mergeRecursive([1], null));

        $array1 = [0 => "val1"];
        $array2 = [1 => "val2"];
        $this->assertNotEmpty($this->stub->mergeRecursive($array1, $array2));
        $this->assertEquals([0 => "val1", 1 => "val2"], $this->stub->mergeRecursive($array1, $array2));

        $array2   = [
            1 => "val2",
            [
                2 => "val3",
                [
                    3 => "val4",
                ],
            ],
        ];
        $resArray = [
            0 => "val1",
            1 => "val2",
            [
                2 => "val3",
                [
                    3 => "val4",
                ],
            ],
        ];
        $this->assertEquals($resArray, $this->stub->mergeRecursive($array1, $array2));
    }

    /**
     * Test CMbArray::sumArraysByKey
     */
    public function testSumArraysByKey(): void
    {
        $array1 = [
            "a" => 10, "b" => 10, "c" => 10, "d" => 10,
        ];

        $array2 = [
            "a" => 10, "b" => 20, "c" => 30, "d" => 40,
        ];

        $resArray = [
            "a" => 20, "b" => 30, "c" => 40, "d" => 50,
        ];

        $this->assertEquals($resArray, $this->stub->sumArraysByKey([$array1, $array2]));
    }

    /**
     * Test CMbArray::mergeKeys
     */
    public function testMergeKeys(): void
    {
        $this->assertNotEmpty($this->stub->mergeKeys([1], [1]));
        $this->assertEquals([1 => 1, 2 => 1], $this->stub->mergeKeys([1 => 1], [2 => 1]));
        $this->assertEquals([1 => 1, 2 => 1, 3 => 1], $this->stub->mergeKeys([1 => 1], [2 => 1], [3 => 1]));
    }

    /**
     * Test CMbArray::get
     */
    public function testGet(): void
    {
        $this->assertNull($this->stub->get(null, null));
        $this->assertEquals("val", $this->stub->get(["key" => "val"], "key"));
    }

    /**
     * Test CMbArray::first
     */
    public function testFirst(): void
    {
        $this->assertEquals(
            "val1",
            $this->stub->first(
                ["key1" => "val1", "key2" => "val2", "key3" => "val3"],
                ["key1", "key2"]
            )
        );

        $this->assertNull($this->stub->first(["val"], ["notAkey"]));
    }

    /**
     * Test CMbArray::arrayFirst
     */
    public function testArrayFirst(): void
    {
        $expected = 3;
        $fnc      = function ($item) use ($expected) {
            return $item === $expected;
        };

        $this->assertNull($this->stub->arrayFirst($fnc, []));
        $this->assertNull($this->stub->arrayFirst($fnc, [1, 2]));
        $this->assertEquals($expected, $this->stub->arrayFirst($fnc, [1, 2, 3]));
        $this->assertEquals($expected, $this->stub->arrayFirst($fnc, [3, 2, 3]));
        $this->assertEquals($expected, $this->stub->arrayFirst($fnc, [3]));
    }

    public function testExtractThrowException(): void
    {
        $array = ["key" => "val"];
        $this->expectError();
        $this->stub->extract($array, "notAkey", null, true);
    }

    /**
     * Test CMbArray::extract
     */
    public function testExtract(): void
    {
        $array = [
            "key"  => "val",
            "key2" => "val2",
        ];
        $this->assertEquals("val", $this->stub->extract($array, "key"));
        $this->assertNull($this->stub->extract($array, "notAkey"));
    }

    /**
     * Test CMbArray::defaultValue
     */
    public function testDefaultValue(): void
    {
        $array = ["key" => "val"];
        $this->stub->defaultValue($array, "key2", "val2");
        $this->assertArrayHasKey("key2", $array);
        $this->assertEquals("val2", $array["key2"]);
    }

    /**
     * Test CMbArray::makeXmlAttributes
     */
    public function testMakeXmlAttributesHasRightReturn(): void
    {
        $array = ["key" => "val"];
        $this->assertEquals('', $this->stub->makeXmlAttributes([]));
        $this->assertEquals("key=\"val\" ", $this->stub->makeXmlAttributes($array));
    }

    /**
     * Test CMbArray::pluck exception
     * @dataProvider      pluckArray
     */
    public function testPluckThrowException(array $array): void
    {
        $this->expectError();
        $this->stub->pluck($array, "notAprop");
    }

    /**
     * Get array for the pluck test
     *
     * @return array
     */
    public function pluckArray(): array
    {
        return [
            [
                ["key" => new stdClass()],
            ],
            [
                ["key" => "val"],
            ],
            [
                ["key" => ["key2" => "val2"]],
            ],
        ];
    }

    /**
     * Test CMbArray::pluck
     */
    public function testPluck(): void
    {
        $this->assertNull($this->stub->pluck(null, ""));

        $array = [
            "key" => [
                "key2" => "val",
                "key3" => [
                    "key4" => "val",
                ],
            ],
        ];
        $this->assertEquals(["key" => "val"], $this->stub->pluck($array, "key2"));

        $array = [
            'key'  => (object)["property" => 1],
            'key2' => (object)["property" => 2],
        ];
        $this->assertEquals(["key" => 1, "key2" => 2], $this->stub->pluck($array, "property"));
    }

    /**
     * Test CMbArray::filterPrefix
     */
    public function testFilterPrefix(): void
    {
        $this->assertArrayNotHasKey(
            "filtered",
            $this->stub->filterPrefix(["key" => "val", "key2" => "val2", 'filtered' => "val3"], "key")
        );
    }

    /**
     * Test CMbArray::transpose
     */
    public function testTranspose(): void
    {
        $array = [
            ["val1", "val2", "val3"],
            ["val1", "val2", "val3"],
        ];
        $res   = [
            ["val1", "val1"],
            ["val2", "val2"],
            ["val3", "val3"],
        ];
        $this->assertEquals($res, $this->stub->transpose($array));
    }

    /**
     * Test CMbArray::insertAfterKey
     */
    public function testInsertAfterKey(): void
    {
        $array = [
            "key"  => "val",
            "key2" => "val2",
        ];
        $this->stub->insertAfterKey($array, "key", "newKey", "newValue");
        $this->assertArrayHasKey("newKey", $array);
        $this->assertEquals($array["newKey"], "newValue");
    }

    /**
     * Test CMbArray::average
     */
    public function testAverage(): void
    {
        $this->assertNull($this->stub->average('lorem'));
        $this->assertEquals(10, $this->stub->average([5, 10, 15]));
    }

    /**
     * Test CMbArray::variance
     */
    public function testVariance(): void
    {
        $this->assertNull($this->stub->variance('notAnArray'));
        $this->assertEqualsWithDelta(42.050234508528, $this->stub->variance([0, 33, 101]), 0.000000000001);
    }

    /**
     * Test CMbArray::in
     */
    public function testIn(): void
    {
        $this->assertTrue($this->stub->in("val1", ["key1" => "val1", "key2" => "val2"]));
        $this->assertFalse($this->stub->in("notAval", ["key1" => "val1", "key2" => "val2"]));
        $this->assertTrue($this->stub->in("val2", ["key1" => "val1", "key2" => "val2"], true));
        $this->assertFalse($this->stub->in("2", ["key1" => 1, "key2" => 2], true));
        $this->assertTrue($this->stub->in("val2", "val1 val2 val3", true));
    }

    /**
     * Test CMbArray::flip
     */
    public function testFlip(): void
    {
        $this->assertEquals(["val" => ["key"]], $this->stub->flip(["key" => "val"]));
        $this->assertEquals(
            ["val" => ["key", "key2"]],
            $this->stub->flip(["key" => "val", "key2" => "val"])
        );
    }

    /**
     * Test CMbArray::ksortByArray
     */
    public function testKsortByArray(): void
    {
        $array = [
            "key1" => "val1",
            "key2" => "val2",
            "key3" => "val3",
        ];
        $order = ["key3", "key1", "key2"];
        $res   = [
            "key3" => "val3",
            "key1" => "val1",
            "key2" => "val2",
        ];
        $this->assertEquals($res, $this->stub->ksortByArray($array, $order));
    }

    /**
     * Test CMbArray::ksortByProp
     */
    public function testKsortByProp(): void
    {
        $obj1      = new stdClass();
        $obj1->foo = "bar1";

        $obj2      = new stdClass();
        $obj2->foo = "bar2";

        $objects = [$obj2, $obj1];
        $this->assertTrue($this->stub->ksortByProp($objects, "foo"));
        $this->assertEquals([$obj1, $obj2], $objects);

        $obj1      = new stdClass();
        $obj1->foo = "bar";
        $obj1->baz = "bar1";

        $obj2      = new stdClass();
        $obj2->foo = "bar";
        $obj2->baz = "bar2";
        $objects   = [$obj2, $obj1];
        $this->assertTrue($this->stub->ksortByProp($objects, "foo", "baz"));
        $this->assertEquals([$obj1, $obj2], $objects);
    }

    /**
     * Test CMbArray::searchRecursive
     */
    public function testSearchRecursive(): void
    {
        $array = ["key1" => "val1", ["key2" => "val2", ["key3" => "val3"]]];
        $this->assertEquals(["key1"], $this->stub->searchRecursive("val1", $array));
        $this->assertEquals([[["key3"]]], $this->stub->searchRecursive("val3", $array));
    }

    /**
     * Test CMbArray::filterPrefix
     */
    public function testReadFromPath(): void
    {
        $arr = [
            "key1" => "val1",
            "key2" => "val2",
        ];
        $this->assertNull($this->stub->readFromPath(null, null));
        $this->assertEquals("val1", $this->stub->readFromPath($arr, "key1"));
    }

    /**
     * Test CMbArray::countValues
     */
    public function testCountValues(): void
    {
        $array = [
            "key1" => "val",
            "key2" => "val",
            "key3" => "val3",
            "key4" => "val4",
        ];
        $this->assertEquals(2, $this->stub->countValues("val", $array));
    }

    /**
     * Test CMbArray::toJSON
     */
    public function testToJSON(): void
    {
        // Simple string
        $input    = "foo bar baz";
        $expected = '"foo bar baz"';
        $actual   = $this->stub->toJSON($input);
        $this->assertEquals($expected, $actual);

        // Simple float
        $input    = 0.06;
        $expected = '0.06';
        $actual   = $this->stub->toJSON($input);
        $this->assertEquals($expected, $actual);

        // Object
        $input    = (object)[
            "key1" => "val",
            "key2" => 1.0,
            "key3" => false,
            "key4" => null,
            "key5" => true,
        ];
        $expected = '{"key1":"val","key2":1,"key3":false,"key4":null,"key5":true}';
        $actual   = $this->stub->toJSON($input);
        $this->assertEquals($expected, $actual);

        // Numeric array
        $input    = ["foo", "bar", 'baz'];
        $expected = '["foo","bar","baz"]';
        $actual   = $this->stub->toJSON($input);
        $this->assertEquals($expected, $actual);

        // Array, no UTF-8
        $array    = [
            "key1" => "val",
            "key2" => 1.0,
            "key3" => false,
            "key4" => null,
            "key5" => true,
            6      => [],
        ];
        $expected = '{"key1":"val","key2":1,"key3":false,"key4":null,"key5":true,"6":[]}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);

        // UTF-8 in value
        $array    = [
            "key1" => "val",
            "key2" => 1.0,
            "key3" => false,
            "key4" => "éàç\\'\"",
            "key5" => true,
            6      => [],
        ];
        $expected = '{"key1":"val","key2":1,"key3":false,"key4":"\u00e9\u00e0\u00e7\\\\\'\"","key5":true,"6":[]}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);

        // UTF-8 in key and value
        $array    = [
            "key1"     => "val",
            "test é ù" => 1.0,
            "key3"     => false,
            "key4"     => "éàç\\'\"",
            "key5"     => true,
            6          => [],
        ];
        $expected =
            '{"key1":"val","test \u00e9 \u00f9":1,"key3":false,"key4":"\u00e9\u00e0\u00e7\\\\\'\"","key5":true,"6":[]}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);

        // Recursive
        $array    = [
            "key1"     => "val",
            "test é ù" => 1.0,
            "key3"     => false,
            "key4"     => "éàç\\'\"",
            "key5"     => true,
            6          => [
                "key1"     => "val",
                "test é ù" => 1.0,
                "key3"     => false,
                "key4"     => "éàç\\'\"",
                "key5"     => true,
            ],
        ];
        $expected =
            '{"key1":"val","test \u00e9 \u00f9":1,"key3":false,"key4":"\u00e9\u00e0\u00e7\\\\\'\"","key5":true,"6":' .
            '{"key1":"val",' . '"test \u00e9 \u00f9":1,"key3":false,"key4":"\u00e9\u00e0\u00e7\\\\\'\"","key5":true}}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);

        // Recursive with objects
        $array    = [
            "key1"     => "val",
            "test é ù" => 1.0,
            "key3"     => false,
            "key4"     => (object)[
                "object_key1" => "val",
                "object_key3" => false,
                "object_key4" => "éàç",
                "object_key5" => [
                    "key1"     => "val",
                    "test é ù" => 1.0,
                    "key3"     => false,
                    "key4"     => "éàç",
                    "key5"     => true,
                ],
            ],
            "key5"     => true,
            6          => [
                "key1"     => "val",
                "test é ù" => 1.0,
                "key3"     => false,
                "key4"     => "éàç",
                "key5"     => true,
            ],
        ];
        $expected =
            '{"key1":"val","test \u00e9 \u00f9":1,"key3":false,"key4":{"object_key1":"val","object_key3":false,' .
            '"object_key4":"\u00e9\u00e0\u00e7","object_key5":{"key1":"val","test \u00e9 \u00f9":1,"key3":false,' .
            '"key4":"\u00e9\u00e0\u00e7","key5":true}},"key5":true,"6":{"key1":"val","test \u00e9 \u00f9":1,' .
            '"key3":false,"key4":"\u00e9\u00e0\u00e7","key5":true}}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);

        // Recursive with objects referenced twice
        $object = (object)[
            "key" => "éàç",
        ];

        $array    = [
            "key1" => $object,
            6      => $object,
        ];
        $expected = '{"key1":{"key":"\u00e9\u00e0\u00e7"},"6":{"key":"\u00e9\u00e0\u00e7"}}';
        $actual   = $this->stub->toJSON($array);
        $this->assertEquals($expected, $actual);
    }

    public function testPluckSortEmptyArray(): void
    {
        $array = [];
        CMbArray::pluckSort($array, SORT_NATURAL, 'test');
        $this->assertEquals([], $array);
    }

    public function testPluckSortEmptyArrayRemoveDiacritics(): void
    {
        $array = [];
        CMbArray::pluckSort($array, SORT_NATURAL, CMbArray::PLUCK_SORT_REMOVE_DIACRITICS, 'test');
        $this->assertEquals([], $array);
    }

    public function testPluckSortOnArray(): void
    {
        $array = [
            ['value' => 'Établissements', 'other' => 'other'],
            ['value' => 'mbImport', 'other' => 'other'],
            ['value' => 'Téléconsultation', 'other' => 'other'],
            ['value' => 'XDS', 'other' => 'other'],
            ['value' => 'Weda', 'other' => 'other'],
            ['value' => '^module-observationResult-court^', 'other' => 'other'],
        ];

        CMbArray::pluckSort($array, SORT_FLAG_CASE | SORT_NATURAL, 'value');

        // Cannot compare directly arrays because CMbArray:pluckSort preserve all keys
        foreach (
            [
                'mbImport',
                'Téléconsultation',
                'Weda',
                'XDS',
                '^module-observationResult-court^',
                'Établissements',
            ] as $value
        ) {
            $elem = array_shift($array);
            $this->assertEquals($value, $elem['value']);
        }
    }

    public function testPluckSortOnObjects(): void
    {
        $obj1        = new stdClass();
        $obj1->value = 'test';

        $obj2        = new stdClass();
        $obj2->value = 'éval';

        $obj3        = new stdClass();
        $obj3->value = '1234';

        $obj4        = new stdClass();
        $obj4->value = '^test^';

        $array = [
            $obj1,
            $obj2,
            $obj3,
            $obj4,
        ];

        CMbArray::pluckSort($array, SORT_FLAG_CASE | SORT_NATURAL, 'value');

        // Cannot compare directly arrays because CMbArray:pluckSort preserve all keys
        foreach ([$obj3, $obj1, $obj4, $obj2] as $value) {
            $elem = array_shift($array);
            $this->assertEquals($value, $elem);
        }
    }

    public function testPluckSortRemoveDiacritics(): void
    {
        $obj1        = new stdClass();
        $obj1->value = 'test';

        $obj2        = new stdClass();
        $obj2->value = 'éval';

        $obj3        = new stdClass();
        $obj3->value = '1234';

        $obj4        = new stdClass();
        $obj4->value = '^test^';

        $array = [
            $obj1,
            $obj2,
            $obj3,
            $obj4,
        ];

        CMbArray::pluckSort($array, SORT_FLAG_CASE | SORT_NATURAL, 'value', CMbArray::PLUCK_SORT_REMOVE_DIACRITICS);

        // Cannot compare directly arrays because CMbArray:pluckSort preserve all keys
        foreach ([$obj3, $obj2, $obj1, $obj4] as $value) {
            $elem = array_shift($array);
            $this->assertEquals($value, $elem);
        }
    }

    public function testArrayRandValuesMultiple(): void
    {
        $array = [
            ['id' => '198', 'firstname' => 'Jean', 'lastname' => 'Dupont'],
            ['id' => '224', 'firstname' => 'Martin', 'lastname' => 'Durand'],
            ['id' => '362', 'firstname' => 'Richard', 'lastname' => 'Dubois'],
            ['id' => '475', 'firstname' => 'Robert', 'lastname' => 'Deschamps'],
            ['id' => '502', 'firstname' => 'Jean', 'lastname' => 'Dupuis'],
        ];

        // want 2 elements
        $array_rand = CMbArray::arrayRandValues($array, 2);

        $this->assertIsArray($array_rand);
        $this->assertCount(2, $array_rand);
        $this->assertNotEquals($array_rand[0], $array_rand[1]);
    }

    public function testArrayRandValues(): void
    {
        $array = [
            ['id' => '198', 'firstname' => 'Jean', 'lastname' => 'Dupont'],
            ['id' => '224', 'firstname' => 'Martin', 'lastname' => 'Durand'],
            ['id' => '362', 'firstname' => 'Richard', 'lastname' => 'Dubois'],
            ['id' => '475', 'firstname' => 'Robert', 'lastname' => 'Deschamps'],
            ['id' => '502', 'firstname' => 'Jean', 'lastname' => 'Dupuis'],
        ];

        // want 1 element
        $array_rand = CMbArray::arrayRandValues($array);

        $this->assertIsArray($array_rand);
        $this->assertContains($array_rand, $array);
    }

    public function testCycleValue(): void
    {
        $val1 = 'titi';
        $val2 = 'toto';
        $val3 = 'tutu';

        $array = [$val1, $val2, $val3];

        $this->assertEquals($val2, CMbArray::cycleValue($array, $val1));
        $this->assertEquals($val1, CMbArray::cycleValue($array, 'tutu'));
        $this->expectError();
        CMbArray::cycleValue($array, 'tata');
    }

    public function testMedian(): void
    {
        $val1 = '10';
        $val2 = '20';
        $val3 = '30';
        $val4 = '40';
        $val5 = '50';

        $array = [$val1, $val2, $val3, $val4, $val5];

        $this->assertEquals($val3, CMbArray::median($array));
    }

    public function testArrayFlatten(): void
    {
        $array = [
            ['firstname' => 'Jean', 'lastname' => 'Dupont'],
        ];
        $arr   = CMbArray::array_flatten($array);

        $this->assertEquals('Jean', $arr[0]);
        $this->assertEquals('Dupont', $arr[1]);
    }

    public function testNaturalSort(): void
    {
        $array = ['Martin', 'Richard', 'Jean', 'Dupont'];
        $res   = [3 => "Dupont", 2 => "Jean", 0 => "Martin", 1 => "Richard"];

        CMbArray::naturalSort($array);

        $this->assertEquals($res, $array);
    }

    public function testNaturalKeySort(): void
    {
        $array = [3 => 'Martin', 1 => 'Richard', 0 => 'Jean', 2 => 'Dupont'];
        $res   = [0 => "Jean", 1 => "Richard", 2 => "Dupont", 3 => "Martin"];

        CMbArray::naturalKeySort($array);

        $this->assertEquals($res, $array);
    }

    /**
     * @throws CMbException
     */
    public function testSortObjectsByString(): void
    {
        $u1                = new CUser();
        $u1->user_username = "def";
        $u2                = new CUser();
        $u2->user_username = "abc";
        $objs              = [$u1, $u2];

        CMbArray::sortObjectsByString($objs, 'user_username');

        $this->assertEquals("abc", array_shift($objs)->user_username);
    }
}
