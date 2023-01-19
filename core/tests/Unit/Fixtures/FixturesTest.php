<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Fixtures;

use Exception;
use Ox\Cli\Tests\Fixtures\IpsumFixtures;
use Ox\Cli\Tests\Fixtures\SkippedFixtures;
use Ox\Core\CMbArray;
use Ox\Core\CMbModelNotFoundException;
use Ox\Erp\SourceCode\CFixturesReference;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\FixturesSkippedException;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class FixturesTest extends OxUnitTestCase
{
    public function testDescription(): void
    {
        $fixtures = new IpsumFixtures();
        $this->assertEquals('Hello world', $fixtures->getDescription());
    }

    public function testSkipped(): void
    {
        $fixtures = new SkippedFixtures();
        $this->expectException(FixturesSkippedException::class);
        $fixtures->load();
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testLoad(): array
    {
        $fixtures = new IpsumFixtures();
        $this->assertEquals(0, $fixtures->countLogsStore());
        $fixtures->load();
        $this->assertEquals(1, $fixtures->countLogsStore());
        $this->assertTrue($this->invokePrivateMethod($fixtures, 'hasReference', CIdSante400::class, 'this_is_my_ref'));
        $id400 = $this->invokePrivateMethod($fixtures, 'getReference', CIdSante400::class, 'this_is_my_ref');

        $fr                 = new CFixturesReference();
        $fr->object_class   = 'CIdSante400';
        $fr->object_id      = $id400->_id;
        $fr->fixtures_class = str_replace('\\', '\\\\', IpsumFixtures::class);
        $fr->tag            = 'this_is_my_ref';

        $this->assertNotNull($fr->loadMatchingObject());

        return [$fixtures, $id400->_id];
    }

    /**
     * @param array $datas
     *
     * @throws CMbModelNotFoundException
     * @depends testLoad
     */
    public function testPurge(array $datas): void
    {
        [$fixtures, $reference_id] = $datas;

        $this->assertEquals(0, $fixtures->countLogsDelete());
        $fixtures->purge();
        $this->assertEquals(1, $fixtures->countLogsDelete());

        $this->expectException(CMbModelNotFoundException::class);
        CIdSante400::findOrFail($reference_id);
    }

    /**
     * Generate users and store them in cache
     * @return void
     * @throws TestsException|FixturesException
     * @throws Exception
     */
    public function testGenerateUsersWithCache(): void
    {
        $fixtures = new UsersFixtures();

        // want 3 users, 0 exists -> generate 3
        $fixtures->getUsers(3);
        /** @var CMediusers[] $cache1 */
        $cache1 = $this->getPrivateProperty($fixtures, 'users');
        $this->assertCount(3, $cache1);

        $users1 = [];
        foreach ($cache1 as $user) {
            $users1[] = $user->_id;
        }

        // want 5 users, 3 exists -> generate 2
        $fixtures->getUsers(5);
        /** @var CMediusers[] $cache2 */
        $cache2 = $this->getPrivateProperty($fixtures, 'users');
        $this->assertCount(5, $cache2);

        $users2 = [];
        foreach ($cache2 as $user) {
            $users2[] = $user->_id;
        }

        // check if the two users generated in $users2 are not in $users1
        $diff = CMbArray::diffRecursive($users2, $users1);
        $this->assertNotContains($diff, $users1);

        // check if $cache2 is different than $cache1
        $this->assertNotEquals($users1, $users2);

        // purge all created users in this test
        foreach ($cache2 as $user) {
            $user->purge();
        }
    }

    /**
     * Generate 1 user and don't store it in cache -> only returned
     * @return void
     * @throws TestsException|FixturesException
     * @throws Exception
     */
    public function testGenerateUsersWithoutCache(): void
    {
        $fixtures = new UsersFixtures();

        $cache_before = $this->getPrivateProperty($fixtures, 'users');

        // want 1 user, no cache -> force generate 1
        $user = $fixtures->getUser(false);
        $this->assertNotNull($user->_id);

        $cache_after = $this->getPrivateProperty($fixtures, 'users');

        // check if cached users after generate is same as before
        $this->assertEquals($cache_before, $cache_after);

        // purge created user in this test
        $user->purge();
    }

    /**
     * @throws FixturesException
     */
    public function testGenerateUsersAndReturnException(): void
    {
        $fixtures = new UsersFixtures();

        $this->expectExceptionMessageMatches("/^Can't do nothing for you !/");
        $fixtures->getUsers(0);
    }
}
