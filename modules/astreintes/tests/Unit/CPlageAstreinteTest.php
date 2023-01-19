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
use Ox\Mediboard\Astreintes\CPlageAstreinte;
use Ox\Mediboard\Astreintes\Tests\Fixtures\AstreintesFixtures;
use Ox\Tests\OxUnitTestCase;

class CPlageAstreinteTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     * @dataProvider becomeNextProvider
     */
    public function testBecomeNextReturnInt(?string $type_repeat, int $expected): void
    {
        /** @var CPlageAstreinte $plage */
        $plage = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_PARAMED
        );

        $plage->_type_repeat = $type_repeat;
        $actual              = $plage->becomeNext();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param CPlageAstreinte $plage
     * @param int             $expected
     *
     * @throws Exception
     */
    public function testCountDuplicatedPlagesReturnInt(): void
    {
        /** @var CPlageAstreinte $ast_info */
        $ast_info = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_INFO
        );
        $expected = 2 + $ast_info->countDuplicatedPlages();

        $ast_info_duplicate = new CPlageAstreinte();
        $ast_info_duplicate->cloneFrom($ast_info);
        $ast_info_duplicate->start = CMbDT::dateTime("+1 hour", $ast_info_duplicate->start);
        $ast_info_duplicate->end   = CMbDT::dateTime("+1 hour", $ast_info_duplicate->end);
        $this->storeOrFailed($ast_info_duplicate);

        $ast_info_duplicate_2 = new CPlageAstreinte();
        $ast_info_duplicate_2->cloneFrom($ast_info);
        $ast_info_duplicate_2->start = CMbDT::dateTime("+1 hour", $ast_info_duplicate->start);
        $ast_info_duplicate_2->end   = CMbDT::dateTime("+1 hour", $ast_info_duplicate->end);
        $this->storeOrFailed($ast_info_duplicate_2);

        $actual = $ast_info->countDuplicatedPlages();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param CPlageAstreinte $plage
     * @param int             $expected
     *
     * @dataProvider getDureeProvider
     */
    public function testGetDureeReturnArray(CPlageAstreinte $plage, array $expected): void
    {
        $actual = $plage->getDuree();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     * @dataProvider loadRefColorProvider
     * @config [CConfiguration] astreintes General astreinte_technique_color #bababa
     */
    public function testLoadRefColorReturnString(CPlageAstreinte $plage, string $expected): void
    {
        $actual = $plage->loadRefColor();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $color
     * @param string $expected
     *
     * @dataProvider fontColorProvider
     */
    public function testFontColorReturnString(string $color, string $expected): void
    {
        $actual = (new CPlageAstreinte())->getFontColor($color);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param CPlageAstreinte $plage
     * @param int             $expected
     *
     * @dataProvider getHoursProvider
     */
    public function testGetHours(CPlageAstreinte $plage, float $expected): void
    {
        $actual = $plage->getHours();

        $this->assertEqualsWithDelta($expected, $actual, 0.00000000001);
    }

    public function becomeNextProvider(): array
    {
        return [
            "simple"    => [
                CPlageAstreinte::REPETITION_TYPES[0],
                1,
            ],
            "null"      => [
                null,
                1,
            ],
            "sameweek"  => [
                CPlageAstreinte::REPETITION_TYPES[4],
                1,
            ],
            "double"    => [
                CPlageAstreinte::REPETITION_TYPES[1],
                2,
            ],
            "triple"    => [
                CPlageAstreinte::REPETITION_TYPES[2],
                3,
            ],
            "quadruple" => [
                CPlageAstreinte::REPETITION_TYPES[3],
                4,
            ],
        ];
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function getDureeProvider(): array
    {
        $ast_adm     = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $ast_info    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_INFO
        );
        $ast_med     = $this->getObjectFromFixturesReference(CPlageAstreinte::class, AstreintesFixtures::TAG_PONCT_MED);
        $ast_paramed = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_PARAMED
        );
        $ast_tech    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_TECH
        );

        return [
            "ADM"     => [
                $ast_adm,
                [
                    "year"   => 0,
                    "month"  => 0,
                    "week"   => 1,
                    "day"    => 1,
                    "hour"   => 0,
                    "minute" => 0,
                    "second" => 1,
                ],
            ],
            "INFO"    => [
                $ast_info,
                [
                    "year"   => 0,
                    "month"  => 1,
                    "week"   => 0,
                    "day"    => 1,
                    "hour"   => 0,
                    "minute" => 0,
                    "second" => 1,
                ],
            ],
            "MED"     => [
                $ast_med,
                [
                    "year"   => 1,
                    "month"  => 0,
                    "week"   => 0,
                    "day"    => 1,
                    "hour"   => 0,
                    "minute" => 0,
                    "second" => 1,
                ],
            ],
            "PARAMED" => [
                $ast_paramed,
                [
                    "year"   => 0,
                    "month"  => 0,
                    "week"   => 2,
                    "day"    => 0,
                    "hour"   => 23,
                    "minute" => 0,
                    "second" => 24,
                ],
            ],
            "TECH"    => [
                $ast_tech,
                [
                    "year"   => 0,
                    "month"  => 0,
                    "week"   => 1,
                    "day"    => 1,
                    "hour"   => 0,
                    "minute" => 0,
                    "second" => 1,
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function getHoursProvider(): array
    {
        $ast_adm     = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $ast_info    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_INFO
        );
        $ast_med     = $this->getObjectFromFixturesReference(CPlageAstreinte::class, AstreintesFixtures::TAG_PONCT_MED);
        $ast_paramed = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_PARAMED
        );
        $ast_tech    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_TECH
        );

        return [
            "ADM - 191.983"     => [
                $ast_adm,
                191.98333333333,
            ],
            "INFO - 743.983"    => [
                $ast_info,
                743.98333333333,
            ],
            "MED - 8783.983"   => [
                $ast_med,
                8783.983333333334,
            ],
            "PARAMED - 358.983" => [
                $ast_paramed,
                358.983333333333,
            ],
            "TECH - 191.983"   => [
                $ast_tech,
                191.983333333333,
            ],
        ];
    }

    public function fontColorProvider(): array
    {
        return [
            "000000 return FFF" => [
                "000000",
                CPlageAstreinte::ASTREINTES_COLORS[1],
            ],
            "ffffff return 000" => [
                "ffffff",
                CPlageAstreinte::ASTREINTES_COLORS[0],
            ],
            "4f71c8 return FFF" => [
                "4f71c8",
                CPlageAstreinte::ASTREINTES_COLORS[1],
            ],
            "c9be48 return 000" => [
                "c9be48",
                CPlageAstreinte::ASTREINTES_COLORS[0],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function loadRefColorProvider(): array
    {
        $ast_adm     = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_ADMIN
        );
        $ast_info    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_PONCT_INFO
        );
        $ast_med     = $this->getObjectFromFixturesReference(CPlageAstreinte::class, AstreintesFixtures::TAG_PONCT_MED);
        $ast_paramed = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_PARAMED
        );
        $ast_tech    = $this->getObjectFromFixturesReference(
            CPlageAstreinte::class,
            AstreintesFixtures::TAG_REGU_TECH
        );

        return [
            "ADM - fedcba"     => [
                $ast_adm,
                "fedcba",
            ],
            "INFO - 123456"    => [
                $ast_info,
                "123456",
            ],
            "MED - 111111"     => [
                $ast_med,
                "111111",
            ],
            "PARAMED - abdcef" => [
                $ast_paramed,
                "abdcef",
            ],
            "TECH - bababa"    => [
                $ast_tech,
                "bababa",
            ],
        ];
    }
}
