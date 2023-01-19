<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Tests\Unit;

use Ox\Cli\Console\Fixtures\FixturesFinder;
use Ox\Cli\Console\Fixtures\FixturesLoader;
use Ox\Cli\Tests\Fixtures\Dolor\Amet\AmetFixtures;
use Ox\Cli\Tests\Fixtures\Dolor\DolorFixtures;
use Ox\Cli\Tests\Fixtures\IpsumFixtures;
use Ox\Cli\Tests\Fixtures\LoremFixtures;
use Ox\Tests\OxUnitTestCase;

/**
 * To tests, do not test execute command because TestBootstrap already start()
 */
class FixturesLoaderTest extends OxUnitTestCase
{
    public function testFindFixtures(): FixturesLoader
    {
        $finder   = new FixturesFinder(dirname(__DIR__, 3));
        $property = $this->getPrivateProperty($finder, 'fixtures_glob', true);
        $property->setValue([
                                IpsumFixtures::class, // order 50
                                LoremFixtures::class, // order 100
                            ]);

        $loader = new FixturesLoader();
        $loader->setPath(dirname(__DIR__, 3));
        $this->invokePrivateMethod($loader, 'findFixtures');

        $fixtures = $loader->getFixtures();
        $this->assertTrue(count($fixtures) === 2);
        $this->assertTrue(array_key_first($fixtures) === IpsumFixtures::class);

        // Timer is empty
        $this->assertEmpty($this->getPrivateProperty($loader, 'stats')['time']['purge']);
        $this->assertEmpty($this->getPrivateProperty($loader, 'stats')['time']['load']);

        return $loader;
    }

    public function testFindFixturesWithOptions(): FixturesLoader
    {
        $finder   = new FixturesFinder(dirname(__DIR__, 3));
        $property = $this->getPrivateProperty($finder, 'fixtures_glob', true);
        $property->setValue([
                                IpsumFixtures::class,
                                LoremFixtures::class,
                                DolorFixtures::class,
                                AmetFixtures::class,
                            ]);

        $loader = new FixturesLoader();
        $loader->setPath(dirname(__DIR__, 3));
        // we only want fixtures with namespace starting with Ox\Cli\Tests\Fixtures\Dolor
        $loader->setNameSpace("Ox\Cli\Tests\Fixtures\Dolor");
        $loader->setGroups([FixturesFinderTest::GROUP]);
        $this->invokePrivateMethod($loader, 'findFixtures');

        $fixtures = $loader->getFixtures();

        // we only want Dolor and Amet fixtures
        $this->assertTrue(count($fixtures) === 2);
        $this->assertTrue(array_key_first($fixtures) === DolorFixtures::class);

        return $loader;
    }

    /**
     * @depends testFindFixtures
     */
    public function testOrderFixtures(FixturesLoader $loader): void
    {
        $this->invokePrivateMethod($loader, 'orderFixtures');
        $fixtures = $loader->getFixturesOrdered();
        $this->assertTrue($fixtures[0] instanceof LoremFixtures);
    }
}
