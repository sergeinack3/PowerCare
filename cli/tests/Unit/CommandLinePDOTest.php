<?php

/**
 * @package Mediboard\\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Tests\Unit;

use Exception;
use Ox\Cli\CommandLinePDO;
use Ox\Core\CAppUI;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class CommandLinePDOTest extends OxUnitTestCase
{
    private const PREFIX = 'mediboard_';

    /**
     * @return string
     */
    private function getRandomDatabaseName(): string
    {
        return uniqid(self::PREFIX);
    }

    /**
     * @group schedules
     * @return CommandLinePDO
     * @throws Exception
     */
    public function testConstruct(): CommandLinePDO
    {
        $host = CAppUI::conf('db std dbhost');
        $user = CAppUI::conf('db std dbuser');
        $pass = CAppUI::conf('db std dbpass');

        $pdo = new CommandLinePDO($host, $user, $pass);
        $this->assertInstanceOf(CommandLinePDO::class, $pdo);

        return $pdo;
    }

    /**
     * @depends testConstruct
     *
     * @param CommandLinePDO $pdo
     *
     * @group   schedules
     * @throws Exception
     */
    public function testIsDatabaseExistsOk(CommandLinePDO $pdo): void
    {
        $database = CAppUI::conf('db std dbname');
        $this->assertTrue($pdo->isDatabaseExists($database));
    }

    /**
     * @depends testConstruct
     * @group   schedules
     *
     * @param CommandLinePDO $pdo
     */
    public function testIsDatabaseExistsKo(CommandLinePDO $pdo): void
    {
        $this->assertFalse($pdo->isDatabaseExists($this->getRandomDatabaseName()));
    }

    /**
     * @depends testConstruct
     *
     * @param CommandLinePDO $pdo
     *
     * @group   schedules
     *
     */
    public function testGetAllDatabases(CommandLinePDO $pdo): void
    {
        $random_db = $this->getRandomDatabaseName();
        $pdo->createDatabase($random_db);

        $databases = $pdo->getAllDatabases(self::PREFIX);

        $this->assertContains($random_db, $databases);
        $pdo->dropDatabase($random_db);
    }

    /**
     * @depends testConstruct
     *
     * @param CommandLinePDO $pdo
     *
     * @group   schedules
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testQueryDump(CommandLinePDO $pdo): void
    {
        $path    = dirname(__FILE__, 3) . '/sql/mediboard.sql';
        $queries = $this->invokePrivateMethod($pdo, 'queryDump', $path);
        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
    }

    /**
     * @group   schedules
     * @depends testConstruct
     */
    public function testCreateAndDeleteDatabase(CommandLinePDO $pdo): void
    {
        $database = $this->getRandomDatabaseName();
        $this->assertTrue($pdo->createDatabase($database, true));
        $this->assertTrue($pdo->dropDatabase($database));
    }
}
