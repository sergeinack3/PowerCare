<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\IndexLifeManagement;

use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Units\ByteSizeUnitEnum;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class ElasticHotPhaseTest extends OxUnitTestCase
{
    /**
     * @return void
     * @throws ElasticPhaseConfigurationException
     */
    public function testBuildAnEmptyElasticHotPhase(): void
    {
        $hot      = new ElasticHotPhase();
        $expected = [
            "min_age" => "0ms",
            "actions" => [
                "rollover" => [],
            ],
        ];

        $this->assertEquals($expected, $hot->build());
    }

    /**
     * @return void
     * @throws ElasticPhaseConfigurationException
     */
    public function testBuildWithRolloverWithAllTypes(): void
    {
        $hot = new ElasticHotPhase();
        $hot->setRolloverOnMaxAge(1, TimeUnitEnum::DAYS())
            ->setRolloverOnMaxDocuments(1000000)
            ->setRolloverOnPrimaryShardSize(10, ByteSizeUnitEnum::GIGABYTES());
        $expected = [
            "min_age" => "0ms",
            "actions" => [
                "rollover" => [
                    "max_age"                => "1d",
                    "max_docs"               => 1000000,
                    "max_primary_shard_size" => "10gb",
                ],
            ],
        ];

        $this->assertEquals($expected, $hot->build());
    }

    public function testBuildAnHotPhaseWithPriority(): void
    {
        $hot = new ElasticHotPhase();
        $hot->setPriority(100);
        $expected = [
            "min_age" => "0ms",
            "actions" => [
                "rollover"     => [],
                "set_priority" => [
                    "priority" => 100,
                ],
            ],
        ];

        $this->assertEquals($expected, $hot->build());
    }

    /**
     * @return void
     * @throws ElasticPhaseConfigurationException
     * @dataProvider dataProviderHotPhaseWithoutRollover
     */
    public function testBuildAnHotPhaseWithAdditionalParametersWithoutRollover(ElasticHotPhase $hot): void
    {
        $this->expectException(ElasticPhaseConfigurationException::class);

        $hot->build();
    }

    /**
     * @return void
     * @throws ElasticPhaseConfigurationException
     */
    public function testBuildAnReadOnlyHotPhase(): void
    {
        $hot = new ElasticHotPhase();
        $hot->activeReadOnly();
        $hot->setRolloverOnMaxDocuments(100);

        $expected = [
            "min_age" => "0ms",
            "actions" => [
                "rollover" => [
                    "max_docs" => 100,
                ],
                "readonly" => new stdClass(),
            ],
        ];

        $this->assertEquals($expected, $hot->build());
    }


    /**
     * @return array
     */
    public function dataProviderHotPhaseWithoutRollover(): array
    {
        $hot1 = new ElasticHotPhase();
        $hot1->activeReadOnly();
        $hot2 = new ElasticHotPhase();
        $hot2->setForceMerge(1);
        $hot3 = new ElasticHotPhase();
        $hot3->setShrinkOnShardSize(1, ByteSizeUnitEnum::GIGABYTES());
        $hot4 = new ElasticHotPhase();
        $hot4->activeReadOnly();
        $hot4->setForceMerge(1);
        $hot4->setShrinkOnShardCount(3);

        return [
            "readonly"    => [
                $hot1,
            ],
            "force_merge" => [
                $hot2,
            ],
            "shrink"      => [
                $hot3,
            ],
            "all"         => [
                $hot4,
            ],
        ];
    }
}
