<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Tests\Unit;

use Ox\Cli\Console\Fixtures\FixturesDebug;
use Ox\Cli\Console\Fixtures\FixturesFinder;
use Ox\Cli\Tests\Fixtures\IpsumFixtures;
use Ox\Cli\Tests\Fixtures\LoremFixtures;
use Ox\Tests\OxUnitTestCase;
use ReflectionProperty;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FixturesDebugTest extends OxUnitTestCase
{

    /**
     * @return Command
     */
    private function makeCommand(): Command
    {
        $application = new Application();
        $application->add(new FixturesDebug());

        return $application->find('ox-fixtures:debug');
    }

    /**
     * @group schedules
     */
    public function testExecuteSuccess()
    {
        // Moke fixtures glob
        /** @var ReflectionProperty $property */
        $finder   = new FixturesFinder(dirname(__DIR__, 3));
        $property = $this->getPrivateProperty($finder, 'fixtures_glob', true);
        $property->setValue([
                                IpsumFixtures::class,
                                LoremFixtures::class,
                            ]);

        // Command
        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $command_tester->execute(
            [
                '--groups' => [FixturesFinderTest::GROUP],
            ]
        );

        $output = $command_tester->getDisplay();
        $this->assertMatchesRegularExpression('/2 Fixtures/', $output);
    }
}
