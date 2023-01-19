<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Tests\Unit;

use Ox\Cli\Console\Fixtures\FixturesFinder;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class FixturesFinderTest extends OxUnitTestCase
{

    public const GROUP = 'sample_fixtures';

    public function testFindNothing(): void
    {
        $finder   = new FixturesFinder(dirname(__DIR__, 3), [uniqid('group_name')]);
        $fixtures = $finder->find();
        $this->assertTrue(count($fixtures) === 0);
    }

    public function testFindAll(): void
    {
        $finder   = new FixturesFinder(dirname(__DIR__, 3));
        $fixtures = $finder->find();
        $this->assertTrue(count($fixtures) > 0);
    }

    public function testFindAllWithoutGlobbedClasses(): void
    {
        $finder   = new FixturesFinder(dirname(__DIR__, 3));
        $this->invokePrivateMethod($finder, 'setFixturesGlob', []);
        $fixtures = $finder->find();
        $this->assertTrue(count($fixtures) > 0);
    }

    public function testGroups(): void
    {
        $finder = new FixturesFinder(dirname(__DIR__, 3), ['lorem_group']);
        $this->assertTrue($this->invokePrivateMethod($finder, 'hasGroup'));
        $this->assertTrue($this->invokePrivateMethod($finder, 'matchGroup', 'lorem_group'));
    }

    public function testNameSpace(): void
    {
        $finder = new FixturesFinder(dirname(__DIR__, 3), [], "Ox\Cli\Tests\Fixtures\Dolor");
        $this->assertTrue($this->invokePrivateMethod($finder, 'hasNameSpace'));
        $this->assertTrue(
            $this->invokePrivateMethod($finder, 'matchNameSpace', 'Ox\Cli\Tests\Fixtures\Dolor\DolorFixtures')
        );
    }

    public function testGlobAndRequireFixtures(): void
    {
        $finder = new FixturesFinder(dirname(__DIR__, 3), [], "Ox\Cli\Tests\Fixtures\Dolor");

        $previous_declared_classes = get_declared_classes();
        $this->invokePrivateMethod($finder, 'globAndRequireFixtures', dirname(__DIR__, 3) . '/cli/tests/Fixtures/*');

        $fixtures_test = [];
        foreach (array_diff(get_declared_classes(), $previous_declared_classes) as $class) {
            if (str_contains($class, 'Ox\Cli\Tests\Fixtures')) {
                $fixtures_test[] = $class;
            }
        }

        $this->assertContains('Ox\Cli\Tests\Fixtures\Dolor\Amet\AmetFixtures', $fixtures_test);
        $this->assertContains('Ox\Cli\Tests\Fixtures\Dolor\DolorFixtures', $fixtures_test);
    }
}
