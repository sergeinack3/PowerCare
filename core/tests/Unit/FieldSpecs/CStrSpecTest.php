<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FieldSpecs;

use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CStrSpecTest extends OxUnitTestCase
{
    /**
     * @dataProvider checkPropertyProvider
     */
    public function testCheckProperty(CStrSpec $spec, $obj, ?string $expected): void
    {
        $this->assertEquals($expected, $spec->checkProperty($obj));
    }

    public function checkPropertyProvider(): array
    {
        $obj = new stdClass();
        $obj->bar = 'test|';

        $spec_invalid_length = new CStrSpec('foo', 'bar');
        $spec_invalid_length->length = 'test';

        $spec_length = new CStrSpec('foo', 'bar');
        $spec_length->length = 10;

        $spec_invalid_min_length = new CStrSpec('foo', 'bar');
        $spec_invalid_min_length->minLength = 'test';

        $spec_min_length = new CStrSpec('foo', 'bar');
        $spec_min_length->minLength = 10;

        $spec_invalid_max_length = new CStrSpec('foo', 'bar');
        $spec_invalid_max_length->maxLength = 'test';

        $spec_max_length = new CStrSpec('foo', 'bar');
        $spec_max_length->maxLength = 2;

        $spec_delimiter = new CStrSpec('foo', 'bar');
        // chr(124) = |
        $spec_delimiter->delimiter = 124;

        $spec_canonical = new CStrSpec('foo', 'bar');
        $spec_canonical->canonical = true;

        $spec_class = new CStrSpec('foo', 'bar');
        $spec_class->class = true;

        return [
            'Non numeric length'     => [$spec_invalid_length, $obj, "Spécification de longueur invalide (longueur = 'test')"],
            'Not same length'        => [$spec_length, $obj, "N'a pas la bonne longueur 'test|' (longueur souhaitée : 10)"],
            'Non numeric minLength'  => [$spec_invalid_min_length, $obj, "Spécification de longueur minimale invalide (longueur = 'test')"],
            'Too short '             => [$spec_min_length, $obj, "N'a pas la bonne longueur 'test|' (longueur minimale souhaitée : 10)"],
            'Non numeric maxLength ' => [$spec_invalid_max_length, $obj, "Spécification de longueur maximale invalide (longueur = 'test')"],
            'Too long'               => [$spec_max_length, $obj, "N'a pas la bonne longueur 'test|' (longueur maximale souhaitée : 2)"],
            'Empty delimiter'        => [$spec_delimiter, $obj, "Contient des valeurs vides 'test|'"],
            'Canonical error'        => [$spec_canonical, $obj, "Ne doit contenir que des chiffres et des lettres non-accentuées (pas d'espaces)"],
            'Non existing class'     => [$spec_class, $obj, "La classe 'test|' n'existe pas"],
            'Prop is valid'          => [new CStrSpec('foo', 'bar'), $obj, null],
        ];
    }
}
