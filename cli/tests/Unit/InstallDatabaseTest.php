<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Tests\Unit;

use Exception;
use Ox\Cli\CommandLinePDO;
use Ox\Cli\Console\InstallDatabase;
use Ox\Core\CAppUI;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class InstallDatabaseTest
 */
class InstallDatabaseTest extends OxUnitTestCase
{

    private const DATABASE_NAME = 'ox_phpunit_fresh_install';

    /**
     * @return Command
     */
    private function makeCommand(): Command
    {
        // Avoid reloading configs from default values.
        $cmd = $this->getMockBuilder(InstallDatabase::class)
            ->onlyMethods(['requireConfigs'])
            ->getMock();

        $application = new Application();
        $application->add($cmd);

        return $application->find('ox-install:database');
    }

    /**
     * @group schedules
     */
    public function testExecuteWrongPath(): void
    {
        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);

        $this->expectExceptionMessage('is not a valid directory');
        $command_tester->execute([
                                     '--path' => 'toto/tata/titi',
                                 ]);
    }

    /**
     * @group schedules
     */
    public function testExecuteWrongPassword(): void
    {
        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $command_tester->setInputs(['yes', 'toto', 'toto', 'toto']);
        $this->expectExceptionMessage('The password should match min 6 characters alpha & numeric');
        $command_tester->execute([]);
    }

    /**
     * @group schedules
     */
    public function testExecuteBddAlreadyExists(): void
    {
        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $command_tester->setInputs(['yes', 'azerty123', 'yes']);
        $this->expectExceptionMessageMatches('/Database (.*) already exists./');
        $command_tester->execute([]);
    }

    /**
     * @return array
     */
    private function getInputs(): array
    {
        return [
            'host'           => '127.0.0.1',
            'database'       => 'mediboard_test',
            'user'           => 'root',
            'password'       => null,
            'admin_password' => 'azerty123',
        ];
    }

    /**
     * @group schedules
     */
    public function testExecuteWithoutConfig(): void
    {
        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $inputs         = array_values($this->getInputs());
        array_unshift($inputs, 'no'); // use configuration file
        $inputs[] = 'no'; // create database

        $command_tester->setInputs($inputs);
        $command_tester->execute([]);

        $output = $command_tester->getDisplay();

        // check resume
        foreach ($this->getInputs() as $key => $value) {
            $value   = $key === 'admin_password' ? '(\**)' : $value;
            $pattern = '/' . $key . '(.*)' . $value . '/';
            $this->assertMatchesRegularExpression($pattern, $output);
        }

        // not configurations successfully saved
        $this->assertStringEndsWith('Do you confirm this settings, and create the database [Y/n] ?', $output);
    }

    /**
     * @group schedules
     * @throws Exception
     */
    public function testExecuteInvalidDatabase(): void
    {
        $inputs = [
            'no',
            CAppUI::conf('db std dbhost'),
            '&é~é"#',
            CAppUI::conf('db std dbuser'),
            CAppUI::conf('db std dbpass'),
            'azerty123',
            'yes',
        ];

        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $command_tester->setInputs($inputs);

        $this->expectExceptionMessage('Invalid database name (A-Z0-9_)');
        $command_tester->execute([]);
    }

    /**
     * @group schedules
     * @throws Exception
     */
    public function testExecuteSuccess(): void
    {
        $host = CAppUI::conf('db std dbhost');
        $user = CAppUI::conf('db std dbuser');
        $pass = CAppUI::conf('db std dbpass');

        $pdo = new CommandLinePDO($host, $user, $pass);
        if (!$pdo->dropDatabase(static::DATABASE_NAME)) {
            $this->fail('Error in CommandLinePDO');
        }

        $inputs = [
            'no',
            $host,
            static::DATABASE_NAME,
            $user,
            $pass,
            'azerty123',
            'yes',
        ];

        $command        = $this->makeCommand();
        $command_tester = new CommandTester($command);
        $command_tester->setInputs($inputs);
        $command_tester->execute([]);

        $output = $command_tester->getDisplay();

        $this->assertMatchesRegularExpression('/Database successfully created !/', $output);
    }
}
