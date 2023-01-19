<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\IndexLifeManagement;

use Ox\Core\Elastic\IndexLifeManagement\Phases\AbstractElasticActivePhase;
use Ox\Core\Units\ByteSizeUnitEnum;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class AbstractElasticActivePhaseTest extends OxUnitTestCase
{
    /**
     * Show the focus of the shrink will automatic pick the last set
     * @return void
     */
    public function testAddShrinkToAPhase(): void
    {
        /** @var AbstractElasticActivePhase $phase */
        $phase = $this->buildMockPhase();

        $phase->setShrinkOnShardCount(5);
        $phase->setShrinkOnShardSize(500, ByteSizeUnitEnum::MEGABYTES());

        $expected = [
            "min_age" => "10d",
            "actions" => [
                "shrink" => [
                    "max_primary_shard_size" => "500mb",
                ],
            ],
        ];
        $this->assertEquals($expected, $phase->build());

        $phase->setShrinkOnShardCount(10);
        $expected = [
            "min_age" => "10d",
            "actions" => [
                "shrink" => [
                    "number_of_shards" => 10,
                ],
            ],
        ];
        $this->assertEquals($expected, $phase->build());
    }


    public function testPriority(): void
    {
        /** @var AbstractElasticActivePhase $phase */
        $phase = $this->buildMockPhase();

        $phase->setPriority(1);

        $expected = [
            "min_age" => "10d",
            "actions" => [
                "set_priority" => [
                    "priority" => 1,
                ],
            ],
        ];
        $this->assertEquals($expected, $phase->build());
    }

    public function testForceMerge(): void
    {
        /** @var AbstractElasticActivePhase $phase */
        $phase = $this->buildMockPhase();

        $phase->setForceMerge(12);

        $expected = [
            "min_age" => "10d",
            "actions" => [
                "forcemerge" => [
                    "max_num_segments" => 12,
                ],
            ],
        ];
        $this->assertEquals($expected, $phase->build());

        $phase->setForceMerge(5, true);

        $expected = [
            "min_age" => "10d",
            "actions" => [
                "forcemerge" => [
                    "max_num_segments" => 5,
                    "index_codec"      => "best_compression",
                ],
            ],
        ];
        $this->assertEquals($expected, $phase->build());
    }

    public function testActivateReadOnly(): void
    {
        /** @var AbstractElasticActivePhase $phase */
        $phase = $this->buildMockPhase();

        $phase->activeReadOnly();

        $expected = [
            "min_age" => "10d",
            "actions" => [
                "readonly" => new stdClass(),
            ],
        ];
        $this->assertEquals($expected, $phase->build());
    }

    /**
     * @return MockObject
     */
    private function buildMockPhase(): MockObject
    {
        return $this->getMockBuilder(AbstractElasticActivePhase::class)
            ->setConstructorArgs([10, TimeUnitEnum::DAYS()])
            ->onlyMethods([])
            ->getMock();
    }
}
