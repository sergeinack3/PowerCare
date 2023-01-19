<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FieldSpecs;

use Ox\Core\FieldSpecs\CBirthDateSpec;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CBirthDateSpecTest extends OxUnitTestCase
{
    public function testGetValue(): void
    {
        $spec = new CBirthDateSpec('foo', 'bar');

        $obj = new stdClass();
        $obj->bar = null;

        $this->assertEquals('', $spec->getValue($obj));

        $obj->bar = '0000-00-00';
        $this->assertEquals('', $spec->getValue($obj));

        $obj->bar = '1950-01-01';
        $this->assertEquals('01/01/1950', $spec->getValue($obj));
    }

    public function testCheckProperty(): void
    {
        $spec = new CBirthDateSpec('foo', 'bar');

        $obj = new stdClass();
        $obj->bar = null;

        $this->assertEquals('Format de date invalide', $spec->checkProperty($obj));

        $obj->bar = '1830-01-01';
        $this->assertEquals('Année inférieure à 1850', $spec->checkProperty($obj));

        $obj->bar = '1950-02-30';
        $this->assertEquals("La date '1950-02-30' est invalide", $spec->checkProperty($obj));

        $obj->bar = '1950-01-01';
        $this->assertNull($spec->checkProperty($obj));
    }
}
