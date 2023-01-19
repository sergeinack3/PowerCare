<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FieldSpecs;

use Ox\Core\FieldSpecs\CFloatSpec;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CFloatSpecTest extends OxUnitTestCase
{
    /**
     * @dataProvider checkPropertyProvider
     */
    public function testCheckProperty(CFloatSpec $spec, $obj, ?string $expected): void
    {
        $this->assertEquals($expected, $spec->checkProperty($obj));
    }

    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue($obj, array $params, $expected): void
    {
        $spec = new CFloatSpec('foo', 'bar');
        $this->assertEquals($expected, $spec->getValue($obj, $params));
    }

    public function checkPropertyProvider(): array
    {
        $obj_null      = new stdClass();
        $obj_null->bar = null;

        $obj_negative      = new stdClass();
        $obj_negative->bar = -2;

        $obj      = new stdClass();
        $obj->bar = 10.3;

        $spec_pos      = new CFloatSpec('foo', 'bar');
        $spec_pos->pos = true;

        $spec_min      = new CFloatSpec('foo', 'bar');
        $spec_min->min = 20;

        $spec_max      = new CFloatSpec('foo', 'bar');
        $spec_max->max = 10;

        $spec_ok      = new CFloatSpec('foo', 'bar');
        $spec_ok->min = 10;
        $spec_ok->max = 20;

        return [
            'Value is null'          => [new CFloatSpec('foo', 'bar'), $obj_null, "N'est pas une valeur décimale"],
            'Value must be positive' => [$spec_pos, $obj_negative, 'Doit avoir une valeur positive'],
            'Value greater than min' => [$spec_min, $obj, 'Doit avoir une valeur minimale de 20'],
            'Value less than max'    => [$spec_max, $obj, 'Doit avoir une valeur maximale de 10'],
            'Spec valid'             => [$spec_ok, $obj, null],
        ];
    }

    public function getValueProvider(): array
    {
        return [];
    }
}
