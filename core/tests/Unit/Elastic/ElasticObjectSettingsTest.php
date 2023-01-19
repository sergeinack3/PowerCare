<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;
use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticWarmPhase;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Tests\OxUnitTestCase;

class ElasticObjectSettingsTest extends OxUnitTestCase
{
    /**
     * @param ElasticObjectSettings $actual
     * @param array                 $settings
     * @param array                 $ilm
     *
     * @dataProvider settingsDataProvider
     * @return void
     */
    public function testBuildSettings(ElasticObjectSettings $actual, array $settings, bool $ilm): void
    {
        $this->assertEquals($settings, $actual->getElasticSettings());
        $this->assertEquals($ilm, $actual->hasIndexLifeManagement());
    }

    public function testAddILMToSettings(): void
    {
        $settings = new ElasticObjectSettings("test_index_elastic_mediboard");
        $ilm      = new ElasticIndexLifeManager("toto");
        $ilm->setHotPhase(new ElasticHotPhase());
        $settings->addIndexLifeManagement($ilm);

        $ilm_array = [
            "policy" => "toto",
            "body"   => [
                "policy" => [
                    "phases" => [
                        "hot" => [
                            "min_age" => "0ms",
                            "actions" => [
                                "rollover" => []
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertTrue($settings->hasIndexLifeManagement());
        $this->assertEquals($ilm_array, $settings->getIndexLifeManagement()->build());
    }

    public function testAddEmptyILMToSettings(): void
    {
        $settings = new ElasticObjectSettings("test_index_elastic_mediboard");
        $ilm      = new ElasticIndexLifeManager("toto");
        $settings->addIndexLifeManagement($ilm);

        $this->assertTrue($settings->hasIndexLifeManagement());
        $this->expectException(ElasticPhaseConfigurationException::class);
        $settings->getIndexLifeManagement()->build();
    }

    public function settingsDataProvider(): array
    {
        $settings1 = new ElasticObjectSettings("test_index_elastic_mediboard");

        $settings2 = new ElasticObjectSettings("test_index_elastic_mediboard");
        $settings2->setShards(1)->setReplicas(0);

        $settings3 = new ElasticObjectSettings("test_index_elastic_mediboard");
        $ilm       = new ElasticIndexLifeManager($settings3->getILMName());
        $ilm->setHotPhase((new ElasticHotPhase()));
        $ilm->setWarmPhase((new ElasticWarmPhase(30, TimeUnitEnum::DAYS())));
        $settings3->addIndexLifeManagement($ilm);

        return [
            "default"                  => [
                $settings1,
                [
                    "number_of_shards"   => 3,
                    "number_of_replicas" => 1,
                ],
                false,
            ],
            "with shards and replicas" => [
                $settings2,
                [
                    "number_of_shards"   => 1,
                    "number_of_replicas" => 0,
                ],
                false,
            ],
            "with ilm"                 => [
                $settings3,
                [
                    "number_of_shards"   => 3,
                    "number_of_replicas" => 1,
                    "lifecycle"          => [
                        "name"           => $settings3->getILMName(),
                        "rollover_alias" => $settings3->getAliasName(),
                    ],
                ],
                true,
            ],
        ];
    }
}
