<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Stats;

use DateTime;
use Ox\Mediboard\Jfse\ApiClients\StatsClient;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class StatsServiceTest extends UnitTestJfse
{
    public function testMakeAmountDaysLastTransmissionRequest(): void
    {
        $stat_request = [StatRequest::hydrate(["choice" => 1])];

        $this->assertEquals($stat_request, (new StatsService())->makeStatRequestsFromIntChoices([1], null, null));
    }

    public function testMakeAmountPendingInvoicesRequest(): void
    {
        $stat_request = [StatRequest::hydrate(["choice" => 2])];

        $this->assertEquals($stat_request, (new StatsService())->makeStatRequestsFromIntChoices([2], null, null));
    }

    public function testMakeTotalRejectedInvoices(): void
    {
        $stat_request = [StatRequest::hydrate(["choice" => 3, "begin" => new DateTime("2020-12-02")])];

        $begin = new DateTime("2020-12-02");
        $end   = null;

        $this->assertEquals($stat_request, (new StatsService())->makeStatRequestsFromIntChoices([3], $begin, $end));
    }

    public function testMakeAmountAndTotalBetweenDates(): void
    {
        $stat_request = [
            StatRequest::hydrate(
                [
                    "choice" => 4,
                    "begin"  => new DateTime("2020-12-02"),
                    "end"    => new DateTime("2020-12-30"),
                ]
            ),
        ];

        $begin = new DateTime("2020-12-02");
        $end   = new DateTime("2020-12-30");

        $this->assertEquals($stat_request, (new StatsService())->makeStatRequestsFromIntChoices([4], $begin, $end));
    }

    public function testGetStats(): void
    {
        $json = <<<JSON
{
    "method": {
        "output": {
            "lstResultatsStatistiques": [
                {
                    "nbFacturesAttenteTeletrans": 0
                }
            ]
        }
    }
}
JSON;

        $client  = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json)]);
        $service = new StatsService(new StatsClient($client));

        $stat_request = $service->makeStatRequestsFromIntChoices([1], null, null);

        $expected = StatResult::hydrate(["choice" => 1, "amount_invoices_pending_transmission" => 0]);

        $this->assertEquals($expected, $service->getStats(...$stat_request));
    }
}
