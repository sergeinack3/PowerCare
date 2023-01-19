<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Generators;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\Generators\CObjectGenerator;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Populate\Generators\CCompteRenduGenerator;
use Ox\Tests\OxUnitTestCase;


class CObjectGeneratorTest extends OxUnitTestCase
{

    const POPULATE_NS_PREFIX = "Ox\\Mediboard\\Populate\\";

    const EXCLUDED_GENERATORS = [
        CCompteRenduGenerator::class
    ];

    public function testCountGenerators(): void
    {
        $generators = CClassMap::getInstance()->getClassChildren(CObjectGenerator::class);
        $this->assertCount(22, $generators, 'Do not create new generators !');
    }

    public function listGenerators()
    {
        $generators = CClassMap::getInstance()->getClassChildren(CObjectGenerator::class, false, true);
        $datas      = [];
        foreach ($generators as $generator) {
            // Exclude @deprecated generators
            if (!str_starts_with($generator, static::POPULATE_NS_PREFIX)) {
                continue;
            }
            // Excluded generator
            if (in_array($generator, static::EXCLUDED_GENERATORS)) {
                continue;
            }

            $datas[$generator] = [new $generator()];
        }

        return $datas;
    }

    /**
     * @param CObjectGenerator $generator
     *
     * @pref         listDefault br
     *
     * @dataProvider listGenerators
     */
    public function testGenerate(CObjectGenerator $generator): void
    {
        $generator->generate();
        $this->assertNotNull($generator->getObject());
    }
}
