<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FieldSpecs;

use Ox\Core\FieldSpecs\CCodeSpec;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CCodeSpecTest extends OxUnitTestCase
{
    public function testCheckInsee(): void
    {
        $this->assertEquals('Matricule incorrect', CCodeSpec::checkInsee('foo:bar'));
        $this->assertEquals("Matricule incorrect, la clé n'est pas valide", CCodeSpec::checkInsee('122334566677788'));
        $this->assertNull(CCodeSpec::checkInsee('122334566677729'));
    }

    /**
     * @dataProvider checkPropertyProvider
     */
    public function testCheckProperty(CCodeSpec $spec, $obj, $expected): void
    {
        $this->assertEquals($expected, $spec->checkProperty($obj));
    }

    public function checkPropertyProvider(): array
    {
        $obj      = new stdClass();
        $obj->bar = '1111122222344';

        $rib      = new CCodeSpec('foo', 'bar');
        $rib->rib = true;

        $insee        = new CCodeSpec('foo', 'bar');
        $insee->insee = true;

        $siret        = new CCodeSpec('foo', 'bar');
        $siret->siret = true;

        $order               = new CCodeSpec('foo', 'bar');
        $order->order_number = true;

        $none = new CCodeSpec('foo', 'bar');

        return [
            'rib'          => [$rib, $obj, 'Rib incorrect'],
            'insee'        => [$insee, $obj, 'Matricule incorrect'],
            'siret'        => [$siret, $obj, 'Code SIRET incorrect, doit contenir exactement 14 chiffres'],
            'order_number' => [
                $order,
                $obj,
                'Format de numéro de serie incorrect, doit contenir au moins une fois %id',
            ],
            'none'         => [$none, $obj, 'Spécification de code invalide'],
        ];
    }
}
