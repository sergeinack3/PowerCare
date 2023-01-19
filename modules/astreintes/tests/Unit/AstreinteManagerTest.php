<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CPerson;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Astreintes\AstreinteManager;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Astreintes\CPlageAstreinte;
use Ox\Mediboard\Astreintes\Tests\Fixtures\AstreintesFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class AstreinteManagerTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     * @dataProvider computeNextPrevProvider
     */
    public function testComputeNextPrevReturnCorrectIntervals(string $mode, array $expected): void
    {
        $actual = (new AstreinteManager())->computeNextPrev(CMbDT::date(), $mode);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     * @dataProvider loadAstreintesProvider
     */
    public function testLoadAstreintes(array $expected, string $mode, array $type_names, ?int $category_id): void
    {
        $date = CMbDT::date("first day of january");

        $astreinte_manager = new AstreinteManager();

        $actual = $astreinte_manager->loadAstreintes(
            $date,
            $mode,
            $type_names,
            $category_id
        );

        foreach ($expected as $_key) {
            $this->assertArrayHasKey($_key, $actual);
        }
    }

    /**
     * @dataProvider loadAstreintesForUserProvider
     * @throws Exception
     */
    public function testLoadAstreintesForUserReturnArray(CMediusers $user, array $expected): void
    {
        $actual = (new AstreinteManager())->loadAstreintesForUser($user);

        $plains_actual = [];

        foreach ($actual as $_actual) {
            /**@var CPLageAstreinte $_actual */
            $plains_actual[] = $_actual->getPlainFields();
        }

        $plains_expected = [];
        foreach ($expected as $_expected) {
            /**@var CPLageAstreinte $_expected */
            $plains_expected[] = $_expected->getPlainFields();
        }

        $this->assertArrayContentsEquals($plains_expected, $plains_actual);
    }

    /**
     * @param string|null $user_id
     * @param array       $expected
     *
     * @throws CMbModelNotFoundException
     * @dataProvider getPhonesProvider
     */
    public function testGetPhonesFromUserReturnArray(?CPerson $user, array $expected): void
    {
        $actual = (new AstreinteManager())->getPhonesFromUser($user);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     * @dataProvider loadAstreintesDaysProvider
     */
    public function testLoadAstreintesForDays(array $expected, string $date, string $time): void
    {
        $actual = (new AstreinteManager())->loadAstreintesForDays($date, $time);

        foreach ($expected as $_key => $_plage) {
            $this->assertArrayHasKey($_key, $actual);
        }
    }

    /**
     * @throws Exception
     * @dataProvider plageAstreintesProvider
     */
    public function testGetPlageAstreintes(
        CPlageAstreinte $expected,
        CMediusers $user,
        array $users,
        ?int $plage_id,
        ?string $plage_date,
        ?string $plage_hour,
        ?string $plage_minutes
    ): void {
        $actual = (new AstreinteManager())->getPlageAstreintes(
            $user,
            $users,
            $plage_id,
            $plage_date,
            $plage_hour,
            $plage_minutes
        );

        $this->assertEquals($expected->_id, $actual->_id);
    }

    /**
     * @throws Exception
     */
    public function plageAstreintesProvider(): array
    {
        $user_admin = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_ADMIN
        );
        $user_tech  = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_TECH
        );

        $users = [
            $user_admin->_id => $user_admin,
            $user_tech->_id  => $user_tech,
        ];

        $ast_admin = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $ast_tech  = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_TECH
        );

        $new_astreinte = new CPlageAstreinte();

        $new_astreinte->phone_astreinte = "1234";
        $new_astreinte->user_id = $user_tech->_id;
        $new_astreinte->start   = CMbDT::dateTime("first day of january 12:34:56");

        return [
            "plage_id null"    => [
                $new_astreinte,
                $user_tech,
                $users,
                null,
                CMbDT::date("first day of january"),
                "12",
                "34",
            ],
            "astreintes_tech"  => [
                $ast_tech,
                $user_tech,
                $users,
                (int) $ast_tech->_id,
                null,
                null,
                null,

            ],
            "astreintes_admin" => [
                $ast_admin,
                $user_admin,
                $users,
                (int) $ast_admin->_id,
                null,
                null,
                null,
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testGetUsersForPlageAstreinte(): void
    {
        $actual = (new AstreinteManager())->getUsersForPlageAstreinte();

        $this->assertNotEmpty($actual);
    }

    /**
     * @throws Exception
     */
    public function getPhonesProvider(): array
    {
        $user_admin = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_ADMIN
        );
        $user_tech  = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_TECH
        );

        return [
            "null"       => [
                new CUser(),
                [],
            ],
            "user_admin" => [
                $user_admin,
                ["CUser-user_astreinte" => "1234"],
            ],
            "user_tech"  => [
                $user_tech,
                ["CUser-user_astreinte" => "1234"],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesDaysProvider(): array
    {
        $ast_med = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_MED
        );

        /** @var CPlageAstreinte $ast_med */
        $ast_med->loadRefUser();
        $ast_med->loadRefColor();
        $ast_med->getCollisions();
        $ast_med->loadRefCategory();

        return [
            "01/01 01:00" => [
                [$ast_med->_id => $ast_med],
                CMbDT::date("first day of january"),
                CMbDT::time("01:00:00"),
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesForUserProvider(): array
    {
        $user_admin = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_ADMIN
        );
        $user_tech  = $this->getObjectFromFixturesReference(
            CMediusers::class,
            AstreintesFixtures::TAG_USER_TECH
        );

        $astreintes_admin = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $astreintes_tech  = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_TECH
        );

        foreach ([$astreintes_admin, $astreintes_tech] as $_astreinte) {
            $_astreinte->loadRefUser();
            $_astreinte->loadRefColor();
            $_astreinte->getCollisions();
            $_astreinte->loadRefCategory();
        }

        return [
            "user_admin" => [
                $user_admin,
                [
                    $astreintes_admin->_id => $astreintes_admin,
                ],
            ],
            "user_tech"  => [
                $user_tech,
                [
                    $astreintes_tech->_id => $astreintes_tech,
                ],
            ],
        ];
    }

    public function computeNextPrevProvider(): array
    {
        return [
            "offline" => [
                AstreinteManager::MODE_OFFLINE,
                [
                    CMbDT::date("first day of this month"),
                    CMbDT::date("last day of this month"),
                ],
            ],
            "year"    => [
                AstreinteManager::MODE_YEAR,
                [
                    CMbDT::date("-1 YEAR"),
                    CMbDT::date("+1 YEAR"),
                ],
            ],
            "month"   => [
                AstreinteManager::MODE_MONTH,
                [
                    CMbDT::date("first day of -1 MONTH"),
                    CMbDT::date("first day of +1 MONTH"),
                ],
            ],
            "week"    => [
                AstreinteManager::MODE_WEEK,
                [
                    CMbDT::date("-1 WEEK"),
                    CMbDT::date("+1 WEEK"),
                ],
            ],
            "day"     => [
                AstreinteManager::MODE_DAY,
                [
                    CMbDT::date("-1 DAY"),
                    CMbDT::date("+1 DAY"),
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesProvider(): array
    {
        $ast_info = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_INFO
        );

        $ast_admin = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $ast_med   = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_MED
        );

        foreach ([$ast_info, $ast_admin, $ast_med] as $_astreinte) {
            $_astreinte->loadRefUser();
            $_astreinte->loadRefColor();
            $_astreinte->getCollisions();
            $_astreinte->loadRefCategory();
        }

        $cat_lorem = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_LOREM
        );
        $cat_ipsum = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_IPSUM
        );

        return [
            "year mode - all - no category"        => [
                [
                    $ast_med->_id,
                    $ast_info->_id,
                ],
                AstreinteManager::MODE_WEEK,
                ["all"],
                null,
            ],
            "year mode - type admin - cat_lorem"   => [
                [$ast_admin->_id],
                AstreinteManager::MODE_YEAR,
                [CPlageAstreinte::TYPES_ASTREINTES[0]],
                (int) $cat_lorem->_id,
            ],
            "month mode - type info - no category" => [
                [$ast_info->_id],
                AstreinteManager::MODE_MONTH,
                [CPlageAstreinte::TYPES_ASTREINTES[1]],
                null,
            ],
            "week mode - type med - cat ipsum"     => [
                [$ast_med->_id],
                AstreinteManager::MODE_WEEK,
                [CPlageAstreinte::TYPES_ASTREINTES[2]],
                (int) $cat_ipsum->_id,
            ],
            "day mode - type med - no category"    => [
                [$ast_med->_id],
                AstreinteManager::MODE_DAY,
                [CPlageAstreinte::TYPES_ASTREINTES[2]],
                null,
            ],
        ];
    }
}
