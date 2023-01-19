<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Stats;

use DateTime;
use Exception;
use Ox\Mediboard\Jfse\ApiClients\StatsClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\Stats\StatsServiceException;
use Ox\Mediboard\Jfse\Mappers\StatsMapper;

class StatsService extends AbstractService
{
    /** @var StatsClient */
    protected $client;

    public function __construct(StatsClient $client = null)
    {
        parent::__construct($client ?? new StatsClient());
    }

    public function makeStatRequestsFromIntChoices(array $choices, ?DateTime $begin, ?DateTime $end): array
    {
        $stat_requests = [];
        foreach ($choices as $_choice) {
            switch ($_choice) {
                case StatRequest::AMOUNT_DAYS_LAST_TRANSMISSION:
                    $stat_requests[] = $this->makeAmountDaysLastTransmissionRequest();
                    break;
                case StatRequest::AMOUNT_PENDING_INVOICES:
                    $stat_requests[] = $this->makeAmountPendingInvoicesRequest();
                    break;
                case StatRequest::TOTAL_REJECTED_INVOICES:
                    $stat_requests[] = $this->makeTotalRejectedInvoices($begin, $end);
                    break;
                case StatRequest::AMOUNT_INVOICES_BETWEEN_DATES:
                    $stat_requests[] = $this->makeAmountAndTotalBetweenDates($begin, $end);
                    break;
                default:
                    StatsServiceException::choiceNotFound();
            }
        }

        return $stat_requests;
    }

    private function makeAmountDaysLastTransmissionRequest(): StatRequest
    {
        return StatRequest::hydrate(["choice" => StatRequest::AMOUNT_DAYS_LAST_TRANSMISSION]);
    }

    private function makeAmountPendingInvoicesRequest(): StatRequest
    {
        return StatRequest::hydrate(["choice" => StatRequest::AMOUNT_PENDING_INVOICES]);
    }

    private function makeTotalRejectedInvoices(?DateTime $begin, ?DateTime $end): StatRequest
    {
        return StatRequest::hydrate(
            [
                "choice" => StatRequest::TOTAL_REJECTED_INVOICES,
                "begin"  => $begin,
                "end"    => $end,
            ]
        );
    }

    private function makeAmountAndTotalBetweenDates(DateTime $begin, DateTime $end): StatRequest
    {
        return StatRequest::hydrate(
            [
                "choice" => StatRequest::AMOUNT_INVOICES_BETWEEN_DATES,
                "begin"  => $begin,
                "end"    => $end,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function getStats(StatRequest ...$stats_request): StatResult
    {
        $response = $this->client->getStats($stats_request);

        return (new StatsMapper())->arrayToStatResults($response->getContent());
    }
}
