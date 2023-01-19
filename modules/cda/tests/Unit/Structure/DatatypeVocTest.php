<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Voc;
use Ox\Tests\OxUnitTestCase;

class DatatypeVocTest extends OxUnitTestCase
{
    public function testDatatypeClassesVoc(): void
    {
        $this->createTestClassesVoc('Voc');
    }

    /**
     * Permet de lancer les tests de toutes les classes renseignées
     *
     * @param string $test Datatype
     *
     * @return void
     */
    private function createTestClassesVoc(string $test): void
    {
        $path = "modules/cda/classes/Datatypes/$test/*.php";

        $file = glob($path, defined('GLOB_BRACE') ? GLOB_BRACE : 0);

        foreach ($file as $_file) {
            $_file = CMbArray::get(explode(".", $_file), 0);
            $_file = substr($_file, strrpos($_file, "/") + 1);

            $class = new $_file();
            $this->commonDatatypeBase($class);
        }
    }

    private function commonDatatypeBase(CCDA_Datatype_Voc $datatype_base): void
    {
        // Test avec une valeur null
        $this->assertFalse($datatype_base->validate());

        // Test avec une valeur vide
        $datatype_base->setData(" ");
        $this->assertFalse($datatype_base->validate());

        // Test avec une valeur incorrecte
        $datatype_base->setData("une valeur incorrecte");
        $this->assertFalse($datatype_base->validate());

        // Test avec une valeur correcte
        if ($datatype_base->_enumeration) {
            $datatype_base->setData(CMbArray::get($datatype_base->_enumeration, 0));
            $this->assertTrue($datatype_base->validate());
        }

        // Test avec une valeur correcte d'un union
        $union = $datatype_base->getUnion();
        if ($union) {
            $unionName = "CCDA" . CMbArray::get($union, 0);
            /** @var CCDA_Datatype_Voc $unionClass */
            $unionClass = new $unionName();

            $unionEnum = $unionClass->getEnumeration(true);
            if ($unionEnum) {
                $datatype_base->setData($unionEnum[0]);
                $this->assertTrue($datatype_base->validate());
            }
        }
    }
}
