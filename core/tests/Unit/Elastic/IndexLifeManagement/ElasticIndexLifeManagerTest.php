<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\IndexLifeManagement;

use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;
use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticColdPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticDeletePhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticWarmPhase;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class ElasticIndexLifeManagerTest extends OxUnitTestCase
{
    public function testBuildAnEmptyILM(): void
    {
        $this->expectException(ElasticPhaseConfigurationException::class);

        $ilm = new ElasticIndexLifeManager("toto");
        $ilm->build();
    }

    public function testBuildWithEveryPhase(): void
    {
        $ilm = new ElasticIndexLifeManager("test");
        $hot = new ElasticHotPhase();
        $hot->setRolloverOnMaxAge(1, TimeUnitEnum::DAYS());
        $ilm->setHotPhase($hot);
        $warm = new ElasticWarmPhase(7, TimeUnitEnum::DAYS());
        $ilm->setWarmPhase($warm);
        $cold = new ElasticColdPhase(14, TimeUnitEnum::DAYS());
        $ilm->setColdPhase($cold);
        $delete = new ElasticDeletePhase(30, TimeUnitEnum::DAYS());
        $ilm->setDeletePhase($delete);

        $actual   = $ilm->build();
        $expected = [
            'policy' => 'test',
            'body'   => [
                'policy' => [
                    'phases' => [
                        'hot'    => [
                            'min_age' => '0ms',
                            'actions' => [
                                'rollover' => [
                                    'max_age' => '1d',
                                ],
                            ],
                        ],
                        'warm'   => [
                            'min_age' => '7d',
                            'actions' => new stdClass(),
                        ],
                        'cold'   => [
                            'min_age' => '14d',
                            'actions' => new stdClass(),
                        ],
                        'delete' => [
                            'min_age' => '30d',
                            'actions' => [
                                'delete' => [
                                    'delete_searchable_snapshot' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }
}
