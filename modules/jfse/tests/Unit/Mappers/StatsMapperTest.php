<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use Ox\Mediboard\Jfse\Domain\Stats\StatRequest;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class StatsMapperTest extends UnitTestJfse
{
    public function testStatsRequestToArray(): void
    {
        $expected = [
            "getStatistiques" => [
                "lstStatistiques" => [
                    [
                        "choix"     => 4,
                        "dateDebut" => "20201202",
                        "dateFin"   => "20201230",
                    ],
                ],
            ],
        ];

        $stat_request = StatRequest::hydrate(
            [
                "choice" => 4,
                "begin"  => new DateTime("2020-12-02"),
                "end"    => new DateTime("2020-12-30"),
            ]
        );

        $this->assertEquals($expected, (new StatsMapper())->statsRequestsToArray([$stat_request]));
    }
}
