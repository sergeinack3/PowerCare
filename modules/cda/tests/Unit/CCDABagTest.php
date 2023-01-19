<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Ox\Interop\Cda\CCDABag;
use Ox\Tests\OxUnitTestCase;

class CCDABagTest extends OxUnitTestCase
{
    public function providerGet(): array
    {
        return [
            'Get with nothing in bag'     => ['bag' => new CCDABag([]), 'key' => 'lorem', 'expected' => null],
            'Get with key does not exits' =>
                ['bag' => new CCDABag(['foo' => 'bar']), 'key' => 'lorem', 'expected' => null],
            'Get number int'              => ['bag' => new CCDABag(['foo' => 1]), 'key' => 'foo', 'expected' => 1],
            'Get number string'           => ['bag' => new CCDABag(['foo' => "1"]), 'key' => 'foo', 'expected' => '1'],
            'Get number boolean (true)'
                                          => [
                'bag'      => new CCDABag(['foo' => true]),
                'key'      => 'foo',
                'expected' => true,
            ],
            'Get number boolean (false)'  => [
                'bag'      => new CCDABag(['foo' => false]),
                'key'      => 'foo',
                'expected' => false,
            ],
        ];
    }

    /**
     * @param CCDABag $bag
     * @param string  $key
     * @param mixed   $expected
     *
     * @dataProvider providerGet
     */
    public function testGet(CCDABag $bag, string $key, $expected): void
    {
        $this->assertEquals($expected, $bag->get($key));
    }

    public function testSet(): void
    {
        $bag      = new CCDABag();
        $key      = 'foo';
        $expected = 'bar';
        $bag->set($key, $expected);

        $this->assertEquals($expected, $bag->get($key));

        $bag->set($key, null);
        $this->assertNull($bag->get($key));
    }

    public function providerAll(): array
    {
        $several_entries = [
            'bool_true'  => true,
            'bool_false' => false,
            'number_int' => 1,
            'number_str' => '1',
        ];

        return [
            'Nothing'         => ['bag' => new CCDABag(), 'expected' => []],
            'Empty array'     => ['bag' => new CCDABag([]), 'expected' => []],
            'One entry'       => ['bag' => new CCDABag(['foo' => 'bar']), 'expected' => ['foo' => 'bar']],
            'Several entries' => ['bag' => new CCDABag($several_entries), 'expected' => $several_entries],
        ];
    }

    /**
     * @param CCDABag $bag
     * @param array   $expected
     *
     * @dataProvider providerAll
     */
    public function testAll(CCDABag $bag, array $expected): void
    {
        $this->assertEquals($bag->all(), $expected);
    }

    public function providerMerge(): array
    {
        return [
            'Empty parameter and no bag'            => ['parameters' => [], 'bag' => null, 'expected' => []],
            'Parameter and no bag'                  =>
                ['parameters' => ['foo' => 'bar'], 'bag' => null, 'expected' => ['foo' => 'bar']],
            'Parameter and empty bag'               =>
                ['parameters' => ['foo' => 'bar'], 'bag' => new CCDABag(), 'expected' => ['foo' => 'bar']],
            'Parameter and bag'                     =>
                [
                    'parameters' => ['foo' => 'bar'],
                    'bag'        => new CCDABag(['lorem' => 'ipsum']),
                    'expected'   => ['foo' => 'bar', 'lorem' => 'ipsum'],
                ],
            'Parameter and bag with replaced value' =>
                [
                    'parameters' => ['foo' => 'ipsum'],
                    'bag'        => new CCDABag(['foo' => 'bar', 'lorem' => 'test']),
                    'expected'   => ['foo' => 'ipsum', 'lorem' => 'test'],
                ],
        ];
    }

    /**
     * @param array        $parameters
     * @param CCDABag|null $bag
     * @param array        $expected
     *
     * @dataProvider providerMerge
     */
    public function testMerge(array $parameters, ?CCDABag $bag, array $expected): void
    {
        $merged_bag = CCDABag::merge($parameters, $bag);
        $this->assertEquals($expected, $merged_bag->all());

        $this->assertNotSame(
            $merged_bag,
            $bag,
            'The function merged should be return an new bag, references should be not the same'
        );
    }
}

